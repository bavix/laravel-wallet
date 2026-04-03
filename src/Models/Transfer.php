<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use Bavix\Wallet\Enums\TransferStatus;
use Bavix\Wallet\Internal\Observers\TransferObserver;
use function config;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * Class Transfer.
 *
 * @property TransferStatus $status
 * @property ?TransferStatus $status_last
 * @property non-empty-string $discount
 * @property int $deposit_id
 * @property int $withdraw_id
 * @property Wallet $from
 * @property int $from_id
 * @property Wallet $to
 * @property int $to_id
 * @property non-empty-string $uuid
 * @property non-empty-string $fee
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

    /**
     * @var array<int, string>
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

    #[Override]
    public function getTable(): string
    {
        if ((string) $this->table === '') {
            /** @var string $table */
            $table = config('wallet.transfer.table', 'transfers');
            $this->table = $table;
        }

        return parent::getTable();
    }

    /**
     * @return BelongsTo<Wallet, self>
     */
    public function from(): BelongsTo
    {
        /** @var class-string<Wallet> $model */
        $model = config('wallet.wallet.model', Wallet::class);
        /** @var BelongsTo<Wallet, self> $belongsTo */
        $belongsTo = $this->belongsTo($model, 'from_id');

        return $belongsTo;
    }

    /**
     * @return BelongsTo<Wallet, self>
     */
    public function to(): BelongsTo
    {
        /** @var class-string<Wallet> $model */
        $model = config('wallet.wallet.model', Wallet::class);
        /** @var BelongsTo<Wallet, self> $belongsTo */
        $belongsTo = $this->belongsTo($model, 'to_id');

        return $belongsTo;
    }

    /**
     * @return BelongsTo<Transaction, self>
     */
    public function deposit(): BelongsTo
    {
        /** @var class-string<Transaction> $model */
        $model = config('wallet.transaction.model', Transaction::class);
        /** @var BelongsTo<Transaction, self> $belongsTo */
        $belongsTo = $this->belongsTo($model, 'deposit_id');

        return $belongsTo;
    }

    /**
     * @return BelongsTo<Transaction, self>
     */
    public function withdraw(): BelongsTo
    {
        /** @var class-string<Transaction> $model */
        $model = config('wallet.transaction.model', Transaction::class);
        /** @var BelongsTo<Transaction, self> $belongsTo */
        $belongsTo = $this->belongsTo($model, 'withdraw_id');

        return $belongsTo;
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'deposit_id' => 'int',
            'withdraw_id' => 'int',
            'extra' => 'json',
            'status' => TransferStatus::class,
            'status_last' => TransferStatus::class,
        ];
    }

    #[Override]
    protected static function booted(): void
    {
        static::observe(TransferObserver::class);
    }
}
