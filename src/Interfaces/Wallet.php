<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Wallet
{
    /**
     * @param int|string $amount
     */
    public function deposit($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int|string $amount
     */
    public function withdraw($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int|string $amount
     */
    public function forceWithdraw($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int|string $amount
     */
    public function transfer(self $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param int|string $amount
     */
    public function safeTransfer(self $wallet, $amount, ?array $meta = null): ?Transfer;

    /**
     * @param int|string $amount
     */
    public function forceTransfer(self $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param int|string $amount
     */
    public function canWithdraw($amount, bool $allowZero = false): bool;

    /**
     * @return float|int
     */
    public function getBalanceAttribute();

    public function getBalanceIntAttribute(): int;

    public function walletTransactions(): HasMany;

    public function transactions(): MorphMany;

    public function transfers(): MorphMany;
}
