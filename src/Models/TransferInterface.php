<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface TransferInterface
{
    public const STATUS_EXCHANGE = 'exchange';
    public const STATUS_TRANSFER = 'transfer';
    public const STATUS_PAID = 'paid';
    public const STATUS_REFUND = 'refund';
    public const STATUS_GIFT = 'gift';

    public function getTable(): string;

    public function from(): MorphTo;

    public function to(): MorphTo;

    public function deposit(): BelongsTo;

    public function withdraw(): BelongsTo;
}
