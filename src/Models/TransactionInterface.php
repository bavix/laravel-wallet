<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface TransactionInterface
{
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_WITHDRAW = 'withdraw';

    public function getTable(): string;

    public function payable(): MorphTo;

    public function wallet(): BelongsTo;

    public function getAmountIntAttribute(): int;

    public function getAmountFloatAttribute(): string;

    /**
     * @param float|int|string $amount
     */
    public function setAmountFloatAttribute($amount): void;
}
