<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;

interface WalletFloat
{
    /**
     * @param float $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     */
    public function depositFloat(float $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param float $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     */
    public function withdrawFloat(float $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param float $amount
     * @param array|null $meta
     * @param bool $confirmed
     * @return Transaction
     */
    public function forceWithdrawFloat(float $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @param string $status
     * @return Transfer
     */
    public function transferFloat(Wallet $wallet, float $amount, ?array $meta = null): Transfer;

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @param string $status
     * @return null|Transfer
     */
    public function safeTransferFloat(Wallet $wallet, float $amount, ?array $meta = null): ?Transfer;

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @param string $status
     * @return Transfer
     */
    public function forceTransferFloat(Wallet $wallet, float $amount, ?array $meta = null): Transfer;

    /**
     * @param float $amount
     * @return bool
     */
    public function canWithdrawFloat(float $amount): bool;

    /**
     * @return float
     */
    public function getBalanceFloatAttribute(): float;
}
