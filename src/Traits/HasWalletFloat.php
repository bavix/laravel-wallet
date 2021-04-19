<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\WalletService;
use Throwable;

/**
 * Trait HasWalletFloat.
 *
 * @property-read string $balanceFloat
 */
trait HasWalletFloat
{
    use HasWallet;

    /**
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function forceWithdrawFloat(string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        /** @var Wallet $this */
        $math = app(Mathable::class);
        $decimalPlacesValue = app(WalletService::class)->decimalPlacesValue($this);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->forceWithdraw($result, $meta, $confirmed);
    }

    /**
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function depositFloat(string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        /** @var Wallet $this */
        $math = app(Mathable::class);
        $decimalPlacesValue = app(WalletService::class)->decimalPlacesValue($this);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->deposit($result, $meta, $confirmed);
    }

    /**
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws Throwable
     */
    public function withdrawFloat(string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        /** @var Wallet $this */
        $math = app(Mathable::class);
        $decimalPlacesValue = app(WalletService::class)->decimalPlacesValue($this);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->withdraw($result, $meta, $confirmed);
    }

    public function canWithdrawFloat(string $amount): bool
    {
        /** @var Wallet $this */
        $math = app(Mathable::class);
        $decimalPlacesValue = app(WalletService::class)->decimalPlacesValue($this);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->canWithdraw($result);
    }

    /**
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws Throwable
     */
    public function transferFloat(Wallet $wallet, string $amount, ?array $meta = null): Transfer
    {
        /** @var Wallet $this */
        $math = app(Mathable::class);
        $decimalPlacesValue = app(WalletService::class)->decimalPlacesValue($this);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->transfer($wallet, $result, $meta);
    }

    public function safeTransferFloat(Wallet $wallet, string $amount, ?array $meta = null): ?Transfer
    {
        /** @var Wallet $this */
        $math = app(Mathable::class);
        $decimalPlacesValue = app(WalletService::class)->decimalPlacesValue($this);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->safeTransfer($wallet, $result, $meta);
    }

    /**
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function forceTransferFloat(Wallet $wallet, string $amount, ?array $meta = null): Transfer
    {
        /** @var Wallet $this */
        $math = app(Mathable::class);
        $decimalPlacesValue = app(WalletService::class)->decimalPlacesValue($this);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->forceTransfer($wallet, $result, $meta);
    }

    public function getBalanceFloatAttribute(): string
    {
        /** @var Wallet $this */
        $math = app(Mathable::class);
        $decimalPlacesValue = app(WalletService::class)->decimalPlacesValue($this);
        $decimalPlaces = app(WalletService::class)->decimalPlaces($this);

        return $math->div($this->balance, $decimalPlaces, $decimalPlacesValue);
    }
}
