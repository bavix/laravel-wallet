<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\CommonService;
use function config;

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
        return $this->forceWithdraw($amount * $this->coefficient(), $meta, $confirmed);
    }

    /**
     * @return float
     */
    private function coefficient(): float
    {
        return config('wallet.package.coefficient', 100.);
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
        return $this->deposit($amount * $this->coefficient(), $meta, $confirmed);
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
        return $this->withdraw($amount * $this->coefficient(), $meta, $confirmed);
    }

    /**
     * @param float $amount
     * @return bool
     */
    public function canWithdrawFloat(float $amount): bool
    {
        return $this->canWithdraw($amount * $this->coefficient());
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
        return $this->transfer($wallet, $amount * $this->coefficient(), $meta);
    }

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @return null|Transfer
     */
    public function safeTransferFloat(Wallet $wallet, float $amount, ?array $meta = null): ?Transfer
    {
        return $this->safeTransfer($wallet, $amount * $this->coefficient(), $meta);
    }

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function forceTransferFloat(Wallet $wallet, float $amount, ?array $meta = null): Transfer
    {
        return $this->forceTransfer($wallet, $amount * $this->coefficient(), $meta);
    }

    /**
     * @return float
     */
    public function getBalanceFloatAttribute(): float
    {
        return $this->balance / $this->coefficient();
    }

}
