<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\WalletService;
use Throwable;

/**
 * Trait HasWalletFloat.
 *
 * @property float $balanceFloat
 */
trait HasWalletFloat
{
    use HasWallet;

    /**
     * @param float|string $amount
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function forceWithdrawFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        /** @var Wallet $this */
        $math = app(MathInterface::class);
        $decimalPlacesValue = app(WalletService::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->forceWithdraw($result, $meta, $confirmed);
    }

    /**
     * @param float|string $amount
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function depositFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        /** @var Wallet $this */
        $math = app(MathInterface::class);
        $decimalPlacesValue = app(WalletService::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->deposit($result, $meta, $confirmed);
    }

    /**
     * @param float|string $amount
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws Throwable
     */
    public function withdrawFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        /** @var Wallet $this */
        $math = app(MathInterface::class);
        $decimalPlacesValue = app(WalletService::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->withdraw($result, $meta, $confirmed);
    }

    /**
     * @param float|string $amount
     */
    public function canWithdrawFloat($amount): bool
    {
        /** @var Wallet $this */
        $math = app(MathInterface::class);
        $decimalPlacesValue = app(WalletService::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->canWithdraw($result);
    }

    /**
     * @param float $amount
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws Throwable
     */
    public function transferFloat(Wallet $wallet, $amount, ?array $meta = null): Transfer
    {
        /** @var Wallet $this */
        $math = app(MathInterface::class);
        $decimalPlacesValue = app(WalletService::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->transfer($wallet, $result, $meta);
    }

    /**
     * @param float $amount
     */
    public function safeTransferFloat(Wallet $wallet, $amount, ?array $meta = null): ?Transfer
    {
        /** @var Wallet $this */
        $math = app(MathInterface::class);
        $decimalPlacesValue = app(WalletService::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->safeTransfer($wallet, $result, $meta);
    }

    /**
     * @param float|string $amount
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function forceTransferFloat(Wallet $wallet, $amount, ?array $meta = null): Transfer
    {
        /** @var Wallet $this */
        $math = app(MathInterface::class);
        $decimalPlacesValue = app(WalletService::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->forceTransfer($wallet, $result, $meta);
    }

    /**
     * @return float|int|string
     */
    public function getBalanceFloatAttribute()
    {
        /** @var Wallet $this */
        $math = app(MathInterface::class);
        $decimalPlacesValue = app(WalletService::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        return $math->div($this->balance, $decimalPlaces, $decimalPlacesValue);
    }
}
