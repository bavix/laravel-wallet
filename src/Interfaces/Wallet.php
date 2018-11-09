<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Wallet
{
    /**
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     */
    public function deposit(int $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     */
    public function withdraw(int $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     */
    public function forceWithdraw(int $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param self $wallet
     * @param int $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function transfer(self $wallet, int $amount, ?array $meta = null): Transfer;

    /**
     * @param self $wallet
     * @param int $amount
     * @param array|null $meta
     * @return null|Transfer
     */
    public function safeTransfer(self $wallet, int $amount, ?array $meta = null): ?Transfer;

    /**
     * @param Wallet $wallet
     * @param int $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function forceTransfer(Wallet $wallet, int $amount, ?array $meta = null): Transfer;

    /**
     * @param int $amount
     * @return bool
     */
    public function canWithdraw(int $amount): bool;

    /**
     * @return int
     */
    public function getBalanceAttribute(): int;

    /**
     * @return MorphMany
     */
    public function transactions(): MorphMany;
}
