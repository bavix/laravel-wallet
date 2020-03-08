<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\MathService;
use Bavix\Wallet\Services\WalletService;

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
        $math = app(MathService::class);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = round($math->mul($amount, $decimalPlaces));
        return $this->forceWithdraw((int)$result, $meta, $confirmed);
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
        $math = app(MathService::class);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = round($math->mul($amount, $decimalPlaces));
        return $this->deposit((int)$result, $meta, $confirmed);
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
        $math = app(MathService::class);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = round($math->mul($amount, $decimalPlaces));
        return $this->withdraw((int)$result, $meta, $confirmed);
    }

    /**
     * @param float $amount
     * @return bool
     */
    public function canWithdrawFloat(float $amount): bool
    {
        $math = app(MathService::class);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = round($math->mul($amount, $decimalPlaces));
        return $this->canWithdraw((int)$result);
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
        $math = app(MathService::class);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = round($math->mul($amount, $decimalPlaces));
        return $this->transfer($wallet, (int)$result, $meta);
    }

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @return null|Transfer
     */
    public function safeTransferFloat(Wallet $wallet, float $amount, ?array $meta = null): ?Transfer
    {
        $math = app(MathService::class);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = round($math->mul($amount, $decimalPlaces));
        return $this->safeTransfer($wallet, (int)$result, $meta);
    }

    /**
     * @param Wallet $wallet
     * @param float $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function forceTransferFloat(Wallet $wallet, float $amount, ?array $meta = null): Transfer
    {
        $math = app(MathService::class);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = round($math->mul($amount, $decimalPlaces));
        return $this->forceTransfer($wallet, (int)$result, $meta);
    }

    /**
     * @return float
     */
    public function getBalanceFloatAttribute(): float
    {
        $math = app(MathService::class);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        return $math->div($this->balance, $decimalPlaces);
    }

}
