<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;

/**
 * Trait HasWalletFloat
 *
 * @package Bavix\Wallet\Traits
 *
 * @property-read float $balanceFloat
 */
trait HasWalletFloat
{
    use HasWallet;

    /**
     * @param float $amount
     * @param array|null $meta
     * @param bool $confirmed
     *
     * @return Transaction
     */
    public function forceWithdrawFloat(float $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        return $this->forceWithdraw($amount * 100, $meta, $confirmed);
    }

    /**
     * @param float $amount
     * @param array|null $meta
     * @param bool $confirmed
     *
     * @return Transaction
     */
    public function depositFloat(float $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        return $this->deposit($amount * 100, $meta, $confirmed);
    }

    /**
     * @param float $amount
     * @param array|null $meta
     * @param bool $confirmed
     *
     * @return Transaction
     */
    public function withdrawFloat(float $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        return $this->withdraw($amount * 100, $meta, $confirmed);
    }

    /**
     * @param float $amount
     * @return bool
     */
    public function canWithdrawFloat($amount): bool
    {
        return $this->canWithdraw($amount * 100);
    }

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @return Transfer
     * @throws
     */
    public function transferFloat(Wallet $wallet, float $amount, ?array $meta = null): Transfer
    {
        return $this->transfer($wallet, $amount * 100, $meta);
    }

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @return null|Transfer
     */
    public function safeTransferFloat(Wallet $wallet, float $amount, ?array $meta = null): ?Transfer
    {
        return $this->safeTransfer($wallet, $amount * 100, $meta);
    }

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function forceTransferFloat(Wallet $wallet, float $amount, ?array $meta = null): Transfer
    {
        return $this->forceTransfer($wallet, $amount * 100, $meta);
    }

    /**
     * @return float
     */
    public function getBalanceFloatAttribute(): float
    {
        return $this->balance / 100;
    }

}
