<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\Service\CastService;
use Bavix\Wallet\Models\Wallet as WalletModel;
use function config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Transaction.
 *
 * @property string      $payable_type
 * @property int         $payable_id
 * @property int         $wallet_id
 * @property string      $uuid
 * @property string      $type
 * @property string      $amount
 * @property int         $amountInt
 * @property string      $amountFloat
 * @property bool        $confirmed
 * @property array       $meta
 * @property Wallet      $payable
 * @property WalletModel $wallet
 */
class Transaction extends Model
{
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';

    /**
     * @var string[]
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

    public function getAmountIntAttribute(): int
    {
        return (int) $this->amount;
    }

    public function getAmountFloatAttribute(): string
    {
        $math = app(MathInterface::class);
        $decimalPlacesValue = app(CastService::class)
            ->getWallet($this->wallet)
            ->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        return $math->div($this->amount, $decimalPlaces);
    }

    /**
     * @param float|int|string $amount
     */
    public function setAmountFloatAttribute($amount): void
    {
        $math = app(MathInterface::class);
        $decimalPlacesValue = app(CastService::class)
            ->getWallet($this->wallet)
            ->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        $this->amount = $math->round($math->mul($amount, $decimalPlaces));
    }
}
