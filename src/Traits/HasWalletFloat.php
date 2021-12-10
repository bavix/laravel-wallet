<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\CastServiceInterface;
use Illuminate\Database\RecordsNotFoundException;

/**
 * Trait HasWalletFloat.
 *
 * @property string $balanceFloat
 */
trait HasWalletFloat
{
    use HasWallet;

    /**
     * @param float|string $amount
     *
     * @throws AmountInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceWithdrawFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        $math = app(MathServiceInterface::class);
        $decimalPlacesValue = app(CastServiceInterface::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->forceWithdraw($result, $meta, $confirmed);
    }

    /**
     * @param float|string $amount
     *
     * @throws AmountInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function depositFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        $math = app(MathServiceInterface::class);
        $decimalPlacesValue = app(CastServiceInterface::class)->getWallet($this)->decimal_places;
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
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function withdrawFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        $math = app(MathServiceInterface::class);
        $decimalPlacesValue = app(CastServiceInterface::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->withdraw($result, $meta, $confirmed);
    }

    /**
     * @param float|string $amount
     */
    public function canWithdrawFloat($amount): bool
    {
        $math = app(MathServiceInterface::class);
        $decimalPlacesValue = app(CastServiceInterface::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->canWithdraw($result);
    }

    /**
     * @param float|string $amount
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function transferFloat(Wallet $wallet, $amount, ?array $meta = null): Transfer
    {
        $math = app(MathServiceInterface::class);
        $decimalPlacesValue = app(CastServiceInterface::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->transfer($wallet, $result, $meta);
    }

    /**
     * @param float|string $amount
     */
    public function safeTransferFloat(Wallet $wallet, $amount, ?array $meta = null): ?Transfer
    {
        $math = app(MathServiceInterface::class);
        $decimalPlacesValue = app(CastServiceInterface::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->safeTransfer($wallet, $result, $meta);
    }

    /**
     * @param float|string $amount
     *
     * @throws AmountInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceTransferFloat(Wallet $wallet, $amount, ?array $meta = null): Transfer
    {
        $math = app(MathServiceInterface::class);
        $decimalPlacesValue = app(CastServiceInterface::class)->getWallet($this)->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        return $this->forceTransfer($wallet, $result, $meta);
    }

    /**
     * @return float|int|string
     */
    public function getBalanceFloatAttribute()
    {
        $math = app(MathServiceInterface::class);
        $wallet = app(CastServiceInterface::class)->getWallet($this);
        $decimalPlacesValue = $wallet->decimal_places;
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        return $math->div($wallet->getBalanceAttribute(), $decimalPlaces, $decimalPlacesValue);
    }
}
