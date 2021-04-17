<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Wallet
{
    /**
     * @throws AmountInvalid
     */
    public function deposit(string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function withdraw(string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @throws AmountInvalid
     */
    public function forceWithdraw(string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @throws AmountInvalid
     */
    public function transfer(self $wallet, string $amount, ?array $meta = null): Transfer;

    /**
     * @throws AmountInvalid
     */
    public function safeTransfer(self $wallet, string $amount, ?array $meta = null): ?Transfer;

    /**
     * @throws AmountInvalid
     */
    public function forceTransfer(Wallet $wallet, string $amount, ?array $meta = null): Transfer;

    public function canWithdraw($amount, bool $allowZero = false): bool;

    public function getBalanceAttribute(): string;

    public function transactions(): MorphMany;
}
