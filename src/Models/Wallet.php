<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use function app;
use function array_key_exists;
use Bavix\Wallet\Interfaces\Confirmable;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Exchangeable;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\IdentifierFactoryServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Traits\CanConfirm;
use Bavix\Wallet\Traits\CanExchange;
use Bavix\Wallet\Traits\CanPayFloat;
use Bavix\Wallet\Traits\HasGift;
use function config;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Str;

/**
 * Class Wallet.
 *
 * @property class-string $holder_type
 * @property int|non-empty-string $holder_id
 * @property string $name
 * @property string $slug
 * @property non-empty-string $uuid
 * @property string $description
 * @property null|array<mixed> $meta
 * @property int $decimal_places
 * @property Model $holder
 * @property non-empty-string $credit
 * @property string $currency
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $updated_at
 * @property DateTimeInterface $deleted_at
 * @property \Illuminate\Database\Eloquent\Relations\HasMany<Transaction, self> $walletTransactions
 * @property \Illuminate\Database\Eloquent\Relations\MorphMany<Transaction, Model> $transactions
 * @property \Illuminate\Database\Eloquent\Relations\HasMany<Transfer, self> $transfers
 * @property \Illuminate\Database\Eloquent\Relations\HasMany<Transfer, self> $receivedTransfers
 *
 * @method int getKey()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<Transaction, self> walletTransactions()
 * @method \Illuminate\Database\Eloquent\Relations\MorphMany<Transaction, Model> transactions()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<Transfer, self> transfers()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany<Transfer, self> receivedTransfers()
 */
class Wallet extends Model implements Customer, WalletFloat, Confirmable, Exchangeable
{
    use CanConfirm;
    use CanExchange;
    use CanPayFloat;
    use HasGift;
    use SoftDeletes;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'holder_type',
        'holder_id',
        'name',
        'slug',
        'uuid',
        'description',
        'meta',
        'balance',
        'decimal_places',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<string, int|string>
     */
    protected $attributes = [
        'balance' => 0,
        'decimal_places' => 2,
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'decimal_places' => 'int',
            'meta' => 'json',
        ];
    }

    #[\Override]
    public function getTable(): string
    {
        if ((string) $this->table === '') {
            /** @var string $table */
            $table = config('wallet.wallet.table', 'wallets');
            $this->table = $table;
        }

        return parent::getTable();
    }

    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = $name;
        /**
         * Must be updated only if the model does not exist or the slug is empty.
         */
        if ($this->exists) {
            return;
        }
        if (array_key_exists('slug', $this->attributes)) {
            return;
        }
        /** @var string $slug */
        $slug = Str::slug($name);
        $this->attributes['slug'] = $slug;
    }

    /**
     * Under ideal conditions, you will never need a method. Needed to deal with out-of-sync.
     *
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function refreshBalance(): bool
    {
        return app(AtomicServiceInterface::class)->block($this, function () {
            /** @var non-empty-string $whatIs */
            $whatIs = $this->getBalanceAttribute();
            /** @var float|int|non-empty-string $balanceRaw */
            $balanceRaw = $this->getAvailableBalanceAttribute();
            /** @var non-empty-string $balance */
            $balance = (string) $balanceRaw;
            if (app(MathServiceInterface::class)->compare($whatIs, $balance) === 0) {
                return true;
            }

            return app(RegulatorServiceInterface::class)->sync($this, $balance);
        });
    }

    public function getOriginalBalanceAttribute(): string
    {
        /** @var int|string|null $rawBalance */
        $rawBalance = $this->getRawOriginal('balance', 0);
        $balance = (string) $rawBalance;

        // Perform assertion to check if balance is not an empty string
        assert($balance !== '', 'Balance should not be an empty string');

        return $balance;
    }

    public function getAvailableBalanceAttribute(): float|int|string
    {
        /** @var float|int|string|null $balance */
        $balance = $this->walletTransactions()
            ->where('confirmed', true)
            ->sum('amount');

        // Perform assertion to check if balance is not an empty string
        if ($balance === null) {
            $balance = '0';
        }
        assert($balance !== '', 'Balance should not be an empty string');

        return $balance;
    }

    /**
     * @return MorphTo<Model, self>
     */
    public function holder(): MorphTo
    {
        return $this->morphTo();
    }

    public function getCreditAttribute(): string
    {
        $credit = (string) ($this->meta['credit'] ?? '0');

        /**
         * Assert that the credit attribute is not an empty string.
         *
         * This is to ensure that the credit attribute always has a value.
         * If the credit attribute is empty, it can cause issues with the math service.
         *
         * @throws \AssertionError If the credit attribute is an empty string.
         */
        // Assert that credit is not an empty string
        // This is to ensure that the credit attribute always has a value
        // If the credit attribute is empty, it can cause issues with the math service
        assert($credit !== '', 'Credit should not be an empty string. It can cause issues with the math service.');

        return $credit;
    }

    public function getCurrencyAttribute(): string
    {
        /** @var string|null $currency */
        $currency = $this->meta['currency'] ?? null;
        if ($currency !== null) {
            return $currency;
        }

        /** @var string $slug */
        $slug = $this->slug;

        return Str::upper($slug);
    }

    protected function initializeMorphOneWallet(): void
    {
        $this->uuid ??= app(IdentifierFactoryServiceInterface::class)->generate();
    }
}
