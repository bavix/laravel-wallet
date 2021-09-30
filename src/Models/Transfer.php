<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use function array_merge;
use function config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Class Transfer.
 *
 * @property string      $status
 * @property int         $discount
 * @property int         $deposit_id
 * @property int         $withdraw_id
 * @property string      $from_type
 * @property int         $from_id
 * @property string      $to_type
 * @property int         $to_id
 * @property string      $uuid
 * @property int         $fee
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
     * @var array
     */
    protected $casts = [
        'deposit_id' => 'int',
        'withdraw_id' => 'int',
    ];

    /**
     * {@inheritdoc}
     */
    public function getCasts(): array
    {
        return array_merge(
            parent::getCasts(),
            config('wallet.transfer.casts', [])
        );
    }

    public function getTable(): string
    {
        if (!$this->table) {
            $this->table = config('wallet.transfer.table', 'transfers');
        }

        return parent::getTable();
    }

    public function from(): MorphTo
    {
        return $this->morphTo();
    }

    public function to(): MorphTo
    {
        return $this->morphTo();
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'deposit_id');
    }

    public function withdraw(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'withdraw_id');
    }
}
