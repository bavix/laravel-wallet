<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\WalletService;
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
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        return $this->forceWithdraw($amount * $decimalPlaces, $meta, $confirmed);
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
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        return $this->deposit($amount * $decimalPlaces, $meta, $confirmed);
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
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        return $this->withdraw($amount * $decimalPlaces, $meta, $confirmed);
    }

    /**
     * @param float $amount
     * @return bool
     */
    public function canWithdrawFloat(float $amount): bool
    {
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        return $this->canWithdraw($amount * $decimalPlaces);
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
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        return $this->transfer($wallet, $amount * $decimalPlaces, $meta);
    }

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @return null|Transfer
     */
    public function safeTransferFloat(Wallet $wallet, float $amount, ?array $meta = null): ?Transfer
    {
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        return $this->safeTransfer($wallet, $amount * $decimalPlaces, $meta);
    }

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function forceTransferFloat(Wallet $wallet, float $amount, ?array $meta = null): Transfer
    {
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        return $this->forceTransfer($wallet, $amount * $decimalPlaces, $meta);
    }

    /**
     * @return float
     */
    public function getBalanceFloatAttribute(): float
    {
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        return $this->balance / $decimalPlaces;
    }

}
