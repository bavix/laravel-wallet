<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use Bavix\Wallet\Enums\TransferStatus;
use function config;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $transfer_id
 * @property int $from_id
 * @property Wallet $from
 * @property int $to_id
 * @property Wallet $to
 * @property TransferStatus $status
 * @property DateTimeInterface $created_at
 * @property DateTimeInterface $updated_at
 * @property Transfer $transfer
 */
#[\Illuminate\Database\Eloquent\Attributes\Fillable([
    'transfer_id',
    'from_id',
    'to_id',
    'status',
    'created_at',
    'updated_at',
])]
final class Purchase extends Model
{
    #[Override]
    public function getTable(): string
    {
        if ((string) $this->table === '') {
            /** @var string $table */
            $table = config('wallet.purchase.table', 'wallet_purchases');
            $this->table = $table;
        }

        return parent::getTable();
    }

    /**
     * @return BelongsTo<Transfer, $this>
     */
    public function transfer(): BelongsTo
    {
        /** @var class-string<Transfer> $model */
        $model = config('wallet.transfer.model', Transfer::class);

        return $this->belongsTo($model, 'transfer_id');
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function from(): BelongsTo
    {
        /** @var class-string<Wallet> $model */
        $model = config('wallet.wallet.model', Wallet::class);

        return $this->belongsTo($model, 'from_id');
    }

    /**
     * @return BelongsTo<Wallet, $this>
     */
    public function to(): BelongsTo
    {
        /** @var class-string<Wallet> $model */
        $model = config('wallet.wallet.model', Wallet::class);

        return $this->belongsTo($model, 'to_id');
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'transfer_id' => 'int',
            'from_id' => 'int',
            'to_id' => 'int',
            'status' => TransferStatus::class,
        ];
    }
}
