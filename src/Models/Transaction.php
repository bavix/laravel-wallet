<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use function array_merge;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Services\FloatService;
use Bavix\Wallet\Services\WalletService;
use function config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Transaction.
 *
 * @property string           $payable_type
 * @property int              $payable_id
 * @property int              $wallet_id
 * @property string           $uuid
 * @property string           $type
 * @property float|int|string $amount
 * @property float            $amountFloat
 * @property bool             $confirmed
 * @property array            $meta
 * @property Wallet           $payable
 * @property WalletModel      $wallet
 */
class Transaction extends Model
{
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';

    /**
     * @var array
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
    ];

    /**
     * @var array
     */
    protected $casts = [
        'wallet_id' => 'int',
        'confirmed' => 'bool',
        'meta' => 'json',
    ];

    /**
     * {@inheritdoc}
     */
    public function getCasts(): array
    {
        return array_merge(
            parent::getCasts(),
            config('wallet.transaction.casts', [])
        );
    }

    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = config('wallet.transaction.table', 'transactions');
        }

        return parent::getTable();
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(config('wallet.wallet.model', WalletModel::class));
    }

    public function getAmountFloatAttribute(): string
    {
        $decimalPlaces = app(WalletService::class)
            ->decimalPlaces($this->wallet)
        ;

        return app(FloatService::class)
            ->balanceIntToFloat((string) $this->amount, (int) $decimalPlaces)
        ;
    }

    /**
     * @param float|int|string $amount
     */
    public function setAmountFloatAttribute($amount): void
    {
        $decimalPlaces = app(WalletService::class)
            ->decimalPlaces($this->wallet)
        ;

        $this->amount = app(FloatService::class)
            ->balanceFloatToInt((string) $amount, (int) $decimalPlaces)
        ;
    }
}
