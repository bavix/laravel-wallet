<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Observers\TransactionObserver;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\CastServiceInterface;
use function config;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Transaction.
 *
 * @property class-string $payable_type
 * @property int|string $payable_id
 * @property int $wallet_id
 * @property non-empty-string $uuid
 * @property string $type
 * @property non-empty-string $amount
 * @property int $amountInt
 * @property non-empty-string $amountFloat
 * @property bool $confirmed
 * @property array<mixed> $meta
 * @property Wallet $payable
 * @property WalletModel $wallet
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $updated_at
 * @property DateTimeInterface $deleted_at
 *
 * @method int getKey()
 */
class Transaction extends Model
{
    use SoftDeletes;

    final public const TYPE_DEPOSIT = 'deposit';

    final public const TYPE_WITHDRAW = 'withdraw';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'payable_type',
        'payable_id',
        'wallet_id',
        'uuid',
        'type',
        'amount',
        'confirmed',
        'meta',
        'created_at',
        'updated_at',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'wallet_id' => 'int',
            'confirmed' => 'bool',
            'meta' => 'json',
        ];
    }

    public function getTable(): string
    {
        if ((string) $this->table === '') {
            $this->table = config('wallet.transaction.table', 'transactions');
        }

        return parent::getTable();
    }

    /**
     * @return MorphTo<Model, self>
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<WalletModel, self>
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(config('wallet.wallet.model', WalletModel::class));
    }

    public function getAmountIntAttribute(): int
    {
        return (int) $this->amount;
    }

    public function getAmountFloatAttribute(): string
    {
        $math = app(MathServiceInterface::class);
        $decimalPlacesValue = app(CastServiceInterface::class)
            ->getWallet($this->wallet)
            ->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        return $math->div($this->amount, $decimalPlaces, $decimalPlacesValue);
    }

    public function setAmountFloatAttribute(float|int|string $amount): void
    {
        $math = app(MathServiceInterface::class);
        $decimalPlacesValue = app(CastServiceInterface::class)
            ->getWallet($this->wallet)
            ->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        $this->amount = $math->round($math->mul($amount, $decimalPlaces));
    }

    protected static function boot(): void
    {
        parent::boot();

        static::observe(TransactionObserver::class);
    }
}
