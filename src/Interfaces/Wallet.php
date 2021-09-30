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
     * @param int|string $amount
     *
     * @throws AmountInvalid
     */
    public function deposit($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function withdraw($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     */
    public function forceWithdraw($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     */
    public function transfer(self $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     */
    public function safeTransfer(self $wallet, $amount, ?array $meta = null): ?Transfer;

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
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

    public function transactions(): MorphMany;
}
