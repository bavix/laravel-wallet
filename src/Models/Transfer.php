<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use Bavix\Wallet\Internal\Observers\TransferObserver;
use function config;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Transfer.
 *
 * @property string $status
 * @property string $status_last
 * @property string $discount
 * @property int $deposit_id
 * @property int $withdraw_id
 * @property Wallet $from
 * @property int $from_id
 * @property Wallet $to
 * @property int $to_id
 * @property string $uuid
 * @property string $fee
 * @property ?array<mixed> $extra
 * @property Transaction $deposit
 * @property Transaction $withdraw
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $updated_at
 * @property DateTimeInterface $deleted_at
 *
 * @method int getKey()
 */
class Transfer extends Model
{
    use SoftDeletes;

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
        'from_id',
        'to_id',
        'uuid',
        'fee',
        'extra',
        'created_at',
        'updated_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'deposit_id' => 'int',
        'withdraw_id' => 'int',
        'extra' => 'json',
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

    protected static function boot(): void
    {
        parent::boot();

        static::observe(TransferObserver::class);
    }
}
