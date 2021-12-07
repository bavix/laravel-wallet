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
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Traits\CanConfirm;
use Bavix\Wallet\Traits\CanExchange;
use Bavix\Wallet\Traits\CanPayFloat;
use Bavix\Wallet\Traits\HasGift;
use function config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\RecordsNotFoundException;
use Illuminate\Support\Str;

/**
 * Class Wallet.
 *
 * @property string                          $holder_type
 * @property int                             $holder_id
 * @property string                          $name
 * @property string                          $slug
 * @property string                          $uuid
 * @property string                          $description
 * @property null|array                      $meta
 * @property int                             $decimal_places
 * @property \Bavix\Wallet\Interfaces\Wallet $holder
 * @property string                          $credit
 * @property string                          $currency
 */
class Wallet extends Model implements Customer, WalletFloat, Confirmable, Exchangeable
{
    use CanConfirm;
    use CanExchange;
    use CanPayFloat;
    use HasGift;

    /**
     * @var string[]
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
    ];

    /**
     * @var array
     */
    protected $casts = [
        'decimal_places' => 'int',
        'meta' => 'json',
    ];

    protected $attributes = [
        'balance' => 0,
        'decimal_places' => 2,
    ];

    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = config('wallet.wallet.table', 'wallets');
        }

        return parent::getTable();
    }

    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = $name;

        /**
         * Must be updated only if the model does not exist
         *  or the slug is empty.
         */
        if (!$this->exists && !array_key_exists('slug', $this->attributes)) {
            $this->attributes['slug'] = Str::slug($name);
        }
    }

    /**
     * Under ideal conditions, you will never need a method.
     * Needed to deal with out-of-sync.
     *
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function refreshBalance(): bool
    {
        return app(AtomicServiceInterface::class)->block($this, function () {
            $whatIs = $this->getBalanceAttribute();
            $balance = $this->getAvailableBalanceAttribute();
            if (app(MathServiceInterface::class)->compare($whatIs, $balance) === 0) {
                return true;
            }

            return app(RegulatorServiceInterface::class)->sync($this, $balance);
        });
    }

    /** @codeCoverageIgnore */
    public function getOriginalBalanceAttribute(): string
    {
        if (method_exists($this, 'getRawOriginal')) {
            return (string) $this->getRawOriginal('balance', 0);
        }

        return (string) $this->getOriginal('balance', 0);
    }

    /**
     * @return float|int
     */
    public function getAvailableBalanceAttribute()
    {
        return $this->walletTransactions()
            ->where('confirmed', true)
            ->sum('amount')
        ;
    }

    /**
     * @deprecated
     * @see getAvailableBalanceAttribute
     * @codeCoverageIgnore
     *
     * @return float|int
     */
    public function getAvailableBalance()
    {
        return $this->getAvailableBalanceAttribute();
    }

    public function holder(): MorphTo
    {
        return $this->morphTo();
    }

    public function getCreditAttribute(): string
    {
        return (string) ($this->meta['credit'] ?? '0');
    }

    public function getCurrencyAttribute(): string
    {
        return $this->meta['currency'] ?? Str::upper($this->slug);
    }
}
