<?php

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
     * @param array|null $meta
     * @param bool $confirmed
     *
     * @return Transaction
     *
     * @throws AmountInvalid
     */
    public function deposit($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int|string $amount
     * @param array|null $meta
     * @param bool $confirmed
     *
     * @return Transaction
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function withdraw($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int|string $amount
     * @param array|null $meta
     * @param bool $confirmed
     *
     * @return Transaction
     *
     * @throws AmountInvalid
     */
    public function forceWithdraw($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param self $wallet
     * @param int|string $amount
     * @param array|null $meta
     *
     * @return Transfer
     *
     * @throws AmountInvalid
     */
    public function transfer(self $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param self $wallet
     * @param int|string $amount
     * @param array|null $meta
     *
     * @return Transfer|null
     *
     * @throws AmountInvalid
     */
    public function safeTransfer(self $wallet, $amount, ?array $meta = null): ?Transfer;

    /**
     * @param Wallet $wallet
     * @param int|string $amount
     * @param array|null $meta
     *
     * @return Transfer
     *
     * @throws AmountInvalid
     */
    public function forceTransfer(Wallet $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param int|string $amount
     * @param bool $allowZero
     *
     * @return bool
     */
    public function canWithdraw($amount, bool $allowZero = null): bool;

    /**
     * @return int|float
     */
    public function getBalanceAttribute();

    /**
     * @return MorphMany
     */
    public function transactions(): MorphMany;
}
