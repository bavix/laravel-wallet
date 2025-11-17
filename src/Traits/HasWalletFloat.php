<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\FormatterServiceInterface;
use Illuminate\Database\RecordsNotFoundException;

/**
 * Trait HasWalletFloat.
 *
 * @property string $balanceFloat
 * @property float $balanceFloatNum
 *
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 *
 * @psalm-require-implements \Bavix\Wallet\Interfaces\WalletFloat
 */
trait HasWalletFloat
{
    use HasWallet;

    /**
     * @param null|array<mixed> $meta
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function forceWithdrawFloat(
        float|int|string $amount,
        ?array $meta = null,
        bool $confirmed = true
    ): Transaction {
        // Get the math service.
        $math = app(MathServiceInterface::class);

        // Get the decimal places value from the wallet.
        $decimalPlacesValue = app(CastServiceInterface::class)
            ->getWallet($this)
            ->decimal_places;

        // Get the decimal places.
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        /** @var float|int|non-empty-string $amountValue */
        $amountValue = $amount;
        $multiplied = $math->mul($amountValue, $decimalPlaces, $decimalPlacesValue);
        $result = $math->round($multiplied);

        // Perform the withdrawal.
        return $this->forceWithdraw($result, $meta, $confirmed);
    }

    /**
     * @param null|array<mixed> $meta
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function depositFloat(float|int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        // Get the math service.
        $math = app(MathServiceInterface::class);

        // Get the decimal places value from the wallet.
        $decimalPlacesValue = app(CastServiceInterface::class)
            ->getWallet($this)
            ->decimal_places;

        // Get the decimal places.
        /** @var non-empty-string $decimalPlaces */
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        // Rounding is needed to avoid issues with floats.
        /** @var float|int|non-empty-string $amountValue */
        $amountValue = $amount;
        /** @var non-empty-string $multiplied */
        $multiplied = $math->mul($amountValue, $decimalPlaces, $decimalPlacesValue);
        /** @var non-empty-string $result */
        $result = $math->round($multiplied);

        // Perform the deposit.
        return $this->deposit($result, $meta, $confirmed);
    }

    /**
     * @param null|array<mixed> $meta
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws BalanceIsEmpty If the balance is empty.
     * @throws InsufficientFunds If the amount exceeds the balance.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function withdrawFloat(float|int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction
    {
        // Get the math service.
        $math = app(MathServiceInterface::class);

        // Get the decimal places value from the wallet.
        $decimalPlacesValue = app(CastServiceInterface::class)
            ->getWallet($this)
            ->decimal_places;

        // Get the decimal places.
        /** @var non-empty-string $decimalPlaces */
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        // Rounding is needed to avoid issues with floats.
        /** @var float|int|non-empty-string $amountValue */
        $amountValue = $amount;
        /** @var non-empty-string $multiplied */
        $multiplied = $math->mul($amountValue, $decimalPlaces, $decimalPlacesValue);
        /** @var non-empty-string $result */
        $result = $math->round($multiplied);

        // Perform the withdrawal.
        return $this->withdraw($result, $meta, $confirmed);
    }

    /**
     * @throws AmountInvalid
     */
    public function canWithdrawFloat(float|int|string $amount): bool
    {
        // Get the math service.
        $math = app(MathServiceInterface::class);

        // Get the decimal places value from the wallet.
        $decimalPlacesValue = app(CastServiceInterface::class)
            ->getWallet($this)
            ->decimal_places;

        // Get the decimal places.
        /** @var non-empty-string $decimalPlaces */
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        // Rounding is needed to avoid issues with floats.
        /** @var float|int|non-empty-string $amountValue */
        $amountValue = $amount;
        /** @var non-empty-string $multiplied */
        $multiplied = $math->mul($amountValue, $decimalPlaces, $decimalPlacesValue);
        /** @var non-empty-string $result */
        $result = $math->round($multiplied);

        // Check if the user can withdraw the specified amount.
        return $this->canWithdraw($result);
    }

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws AmountInvalid If the amount specified is invalid (e.g., negative values).
     * @throws BalanceIsEmpty If the balance of this wallet is empty.
     * @throws InsufficientFunds If the amount to be transferred exceeds the available balance in this wallet.
     * @throws RecordsNotFoundException If the target wallet cannot be found.
     * @throws TransactionFailedException If the transfer could not be completed due to a failure
     *                                     in the underlying transaction system.
     * @throws ExceptionInterface A generic exception interface catch-all for any other exceptions that
     *                            might occur during the execution of the transfer.
     */
    public function transferFloat(
        Wallet $wallet,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): Transfer {
        // Get the math service.
        $math = app(MathServiceInterface::class);

        // Get the decimal places value from the wallet.
        $decimalPlacesValue = app(CastServiceInterface::class)->getWallet($this)->decimal_places;

        // Get the decimal places.
        /** @var non-empty-string $decimalPlaces */
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        // Rounding is needed to avoid issues with floats.
        /** @var float|int|non-empty-string $amountValue */
        $amountValue = $amount;
        /** @var non-empty-string $multiplied */
        $multiplied = $math->mul($amountValue, $decimalPlaces, $decimalPlacesValue);
        /** @var non-empty-string $result */
        $result = $math->round($multiplied);

        // Perform the transfer.
        return $this->transfer($wallet, $result, $meta);
    }

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws AmountInvalid If the amount is invalid.
     */
    public function safeTransferFloat(
        Wallet $wallet,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): ?Transfer {
        // Get the math service.
        $math = app(MathServiceInterface::class);

        // Get the decimal places value from the wallet.
        // This value is used to convert the amount to the correct decimal format.
        $decimalPlacesValue = app(CastServiceInterface::class)->getWallet($this)->decimal_places;

        // Calculate the decimal places value as a power of ten.
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        // Rounding is needed to avoid issues with floats.
        // The result is the amount in the correct decimal format.
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        // Perform the transfer.
        // This method will not throw an exception if the transfer fails. Instead, it will return null.
        return $this->safeTransfer($wallet, $result, $meta);
    }

    /**
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws AmountInvalid If the amount specified is invalid (e.g., negative values).
     * @throws RecordsNotFoundException If the target wallet cannot be found.
     * @throws TransactionFailedException If the transfer could not be completed due to a failure
     * in the underlying transaction system.
     * @throws ExceptionInterface A generic exception interface catch-all for any other exceptions that
     * might occur during the execution of the transfer.
     */
    public function forceTransferFloat(
        Wallet $wallet,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): Transfer {
        // Get the math service.
        $math = app(MathServiceInterface::class);

        // Get the decimal places value from the wallet.
        // This value is used to convert the amount to the correct decimal format.
        $decimalPlacesValue = app(CastServiceInterface::class)->getWallet($this)->decimal_places;

        // Calculate the decimal places value as a power of ten.
        /** @var non-empty-string $decimalPlaces */
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        // Rounding is needed to avoid issues with floats.
        // The result is the amount in the correct decimal format.
        /** @var float|int|non-empty-string $amountValue */
        $amountValue = $amount;
        /** @var non-empty-string $multiplied */
        $multiplied = $math->mul($amountValue, $decimalPlaces, $decimalPlacesValue);
        /** @var non-empty-string $result */
        $result = $math->round($multiplied);

        // Perform the transfer.
        // This method will not throw an exception if the transfer fails. Instead, it will return null.
        return $this->forceTransfer($wallet, $result, $meta);
    }

    /**
     * @return non-empty-string
     */
    public function getBalanceFloatAttribute(): string
    {
        // Get the wallet.
        $wallet = app(CastServiceInterface::class)->getWallet($this, false);

        // Get the wallet balance.
        /** @var non-empty-string $balance */
        $balance = $wallet->getBalanceAttribute();

        // Get the decimal places value from the wallet.
        $decimalPlacesValue = $wallet->decimal_places;

        // Use the formatter service to format the balance as a float.
        /** @var non-empty-string $result */
        $result = app(FormatterServiceInterface::class)->floatValue(
        // The balance of the wallet.
            $balance,

            // The number of decimal places for the wallet.
            $decimalPlacesValue,
        );

        return $result;
    }

    /**
     * Get the balance of the wallet as a float number.
     *
     * This method is used to get the balance of the wallet as a float number. The number of decimal
     * places is obtained from the wallet's `decimal_places` attribute. This method is useful when you
     * need to perform calculations or formatting on the balance.
     *
     * @return float The balance of the wallet as a float number.
     */
    public function getBalanceFloatNumAttribute(): float
    {
        // Get the balance of the wallet as a float number.
        // The number of decimal places is obtained from the wallet's `decimal_places` attribute.
        // This method is useful when you need to perform calculations or formatting on the balance.

        // Return the balance of the wallet as a float.
        return (float) $this->getBalanceFloatAttribute();
    }
}
