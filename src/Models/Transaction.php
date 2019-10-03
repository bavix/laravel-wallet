<?php

namespace Bavix\Wallet\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use function config;

/**
 * Class Transaction
 * @package Bavix\Wallet\Models
 *
 * @property string $payable_type
 * @property int $payable_id
 * @property string $uuid
 * @property string $type
 * @property int $amount
 * @property bool $confirmed
 * @property array $meta
 * @property Wallet $payable
 * @property WalletModel $wallet
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
        'amount' => 'int',
        'confirmed' => 'bool',
        'meta' => 'json'
    ];

    /**
     * @return string
     */
    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = config('wallet.transaction.table', 'transactions');
        }

        return parent::getTable();
    }

    /**
     * @return MorphTo
     */
    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function wallet(): BelongsTo
    {
        return $this->belongsTo(config('wallet.wallet.model', WalletModel::class));
    }

}
