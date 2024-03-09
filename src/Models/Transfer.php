<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use function config;

/**
 * Class Transfer.
 *
 * @property string $status
 * @property string $status_last
 * @property string $discount
 * @property int $deposit_id
 * @property int $withdraw_id
 * @property Wallet $from
 * @property class-string $from_type
 * @property int $from_id
 * @property Wallet $to
 * @property class-string $to_type
 * @property int $to_id
 * @property string $uuid
 * @property string $fee
 * @property Transaction $deposit
 * @property Transaction $withdraw
 *
 * @method int getKey()
 */
class Transfer extends Model
{
    final public const STATUS_EXCHANGE = 'exchange';

    final public const STATUS_TRANSFER = 'transfer';

    final public const STATUS_PAID = 'paid';

    final public const STATUS_REFUND = 'refund';

    final public const STATUS_GIFT = 'gift';

    /**
     * @var array<int,string>
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
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'deposit_id' => 'int',
        'withdraw_id' => 'int',
    ];

    public function getTable(): string
    {
        if ((string) $this->table === '') {
            $this->table = config('wallet.transfer.table', 'transfers');
        }

        return parent::getTable();
    }

    /**
     * @return BelongsTo<Wallet, self>
     */
    public function from(): BelongsTo
    {
        return $this->belongsTo(config('wallet.wallet.model', Wallet::class), 'from_id');
    }

    /**
     * @return BelongsTo<Wallet, self>
     */
    public function to(): BelongsTo
    {
        return $this->belongsTo(config('wallet.wallet.model', Wallet::class), 'to_id');
    }

    /**
     * @return BelongsTo<Transaction, self>
     */
    public function deposit(): BelongsTo
    {
        return $this->belongsTo(config('wallet.transaction.model', Transaction::class), 'deposit_id');
    }

    /**
     * @return BelongsTo<Transaction, self>
     */
    public function withdraw(): BelongsTo
    {
        return $this->belongsTo(config('wallet.transaction.model', Transaction::class), 'withdraw_id');
    }
}
