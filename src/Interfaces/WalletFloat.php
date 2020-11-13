<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;

interface WalletFloat
{
    /**
     * @param float|string $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     */
    public function depositFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param float|string $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     */
    public function withdrawFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param float|string $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     */
    public function forceWithdrawFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param Wallet $wallet
     * @param float|string $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function transferFloat(Wallet $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param Wallet $wallet
     * @param float|string $amount
     * @param array|null $meta
     * @return null|Transfer
     */
    public function safeTransferFloat(Wallet $wallet, $amount, ?array $meta = null): ?Transfer;

    /**
     * @param Wallet $wallet
     * @param float|string $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function forceTransferFloat(Wallet $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param float|string $amount
     * @return bool
     */
    public function canWithdrawFloat($amount): bool;

    /**
     * @return int|float|string
     */
    public function getBalanceFloatAttribute();
}
