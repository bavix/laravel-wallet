<?php

namespace Bavix\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use function config;

/**
 * Class Transfer
 * @package Bavix\Wallet\Models
 *
 * @property string $status
 * @property int $deposit_id
 * @property int $withdraw_id
 * @property string $from_type
 * @property int $from_id
 * @property string $to_type
 * @property int $to_id
 * @property string $uuid
 * @property int $fee
 * @property int $discount
 *
 * @property Transaction $deposit
 * @property Transaction $withdraw
 */
class Transfer extends Model
{

    public const STATUS_EXCHANGE = 'exchange';
    public const STATUS_TRANSFER = 'transfer';
    public const STATUS_PAID = 'paid';
    public const STATUS_REFUND = 'refund';
    public const STATUS_GIFT = 'gift';

    /**
     * @var array
     */
    protected $fillable = [
        'status',
        'discount',
        'deposit_id',
        'withdraw_id',
        'from_type',
        'from_id',
        'to_type',
        'to_id',
        'uuid',
        'fee',
    ];

    /**
     * @return string
     */
    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = config('wallet.transfer.table', 'transfers');
        }

        return parent::getTable();
    }

    /**
     * @return MorphTo
     */
    public function from(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return MorphTo
     */
    public function to(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo
     */
    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'deposit_id');
    }

    /**
     * @return BelongsTo
     */
    public function withdraw(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'withdraw_id');
    }

}
