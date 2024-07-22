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
     * Withdraw funds from the wallet without checking the balance.
     *
     * @param float|int|string $amount The amount to withdraw.
     * @param null|array<mixed> $meta Additional information for the transaction.
     * @param bool $confirmed Whether the transaction is confirmed or not.
     * @return Transaction The created transaction.
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
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        // Perform the withdrawal.
        return $this->forceWithdraw($result, $meta, $confirmed);
    }

    /**
     * Deposit funds into the wallet.
     *
     * This method takes a float or int amount and deposits it into the wallet.
     * It uses the math service to convert the amount to the wallet's decimal format,
     * and then performs the deposit.
     *
     * @param float|int|string $amount The amount to deposit.
     * @param null|array<mixed> $meta Additional information for the transaction.
     * @param bool $confirmed Whether the transaction is confirmed or not.
     * @return Transaction The created transaction.
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
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        // Rounding is needed to avoid issues with floats.
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        // Perform the deposit.
        return $this->deposit($result, $meta, $confirmed);
    }

    /**
     * Withdraw the specified float amount of money from the wallet.
     *
     * This method takes a float or int amount and withdraws it from the wallet.
     * It uses the math service to convert the amount to the wallet's decimal format,
     * and then performs the withdrawal.
     *
     * @param float|int|string $amount The amount to withdraw.
     * @param null|array<mixed> $meta Additional information for the transaction.
     * @param bool $confirmed Whether the transaction is confirmed or not.
     * @return Transaction The created transaction.
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
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        // Rounding is needed to avoid issues with floats.
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        // Perform the withdrawal.
        return $this->withdraw($result, $meta, $confirmed);
    }

    /**
     * Checks if the user can safely withdraw the specified amount of funds.
     *
     * @param float|int|string $amount The amount to withdraw. Can be specified as a float, int, or string.
     * @return bool Returns TRUE if the withdrawal is possible, FALSE otherwise.
     *
     * @throws AmountInvalid If the amount is invalid (e.g., negative values).
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
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        // Rounding is needed to avoid issues with floats.
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        // Check if the user can withdraw the specified amount.
        return $this->canWithdraw($result);
    }

    /**
     * Transfers a specific amount of funds from this wallet to another.
     *
     * This method transfers the specified amount of funds from this wallet to another wallet. The amount can be
     * specified as a float, int, or string. The transferred amount is rounded to the decimal places specified in the
     * wallet's configuration.
     *
     * @param Wallet $wallet The wallet instance to which funds will be transferred.
     * @param float|int|string $amount The amount of funds to transfer. Can be specified as a float, int, or string.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional metadata associated with the transfer. This can be
     *                                                 used to store extra information about the transaction, such as
     *                                                 reasons for the transfer or identifiers linking to other systems.
     * @return Transfer Returns a Transfer object representing the completed transaction.
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
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        // Rounding is needed to avoid issues with floats.
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        // Perform the transfer.
        return $this->transfer($wallet, $result, $meta);
    }

    /**
     * Safely transfers funds from this wallet to another.
     *
     * This method will not throw an exception if the transfer fails. Instead, it will return null.
     *
     * @param Wallet $wallet The wallet to transfer funds to.
     * @param float|int|string $amount The amount to transfer.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional information for the transaction.
     *                                                This can be an instance of an ExtraDtoInterface
     *                                                or an array of arbitrary data.
     * @return Transfer|null The created transaction, or null if the transfer fails.
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
     * Forces a transfer of funds from this wallet to another, bypassing certain safety checks.
     *
     * This method is intended for use in scenarios where a transfer must be completed regardless of
     * the usual validation checks (e.g., sufficient funds, wallet status). It is critical to use this
     * method with caution as it can result in negative balances or other unintended consequences.
     *
     * @param Wallet $wallet The wallet instance to which funds will be transferred.
     * @param float|int|string $amount The amount of funds to transfer. Can be specified as a float, int, or string.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional metadata associated with the transfer. This
     * can be used to store extra information about the transaction, such as reasons for the transfer or
     * identifiers linking to other systems.
     * @return Transfer Returns a Transfer object representing the completed transaction.
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
        $decimalPlaces = $math->powTen($decimalPlacesValue);

        // Convert the amount to the decimal format.
        // Rounding is needed to avoid issues with floats.
        // The result is the amount in the correct decimal format.
        $result = $math->round($math->mul($amount, $decimalPlaces, $decimalPlacesValue));

        // Perform the transfer.
        // This method will not throw an exception if the transfer fails. Instead, it will return null.
        return $this->forceTransfer($wallet, $result, $meta);
    }

    /**
     * Get the balance of the wallet as a float.
     *
     * This method returns the balance of the wallet as a string, and then formats it as a float with the
     * correct number of decimal places. The number of decimal places is obtained from the wallet's
     * `decimal_places` attribute.
     *
     * @return non-empty-string The balance of the wallet as a string, formatted as a float.
     */
    public function getBalanceFloatAttribute(): string
    {
        // Get the wallet.
        $wallet = app(CastServiceInterface::class)->getWallet($this);

        // Get the wallet balance.
        /** @var non-empty-string $balance */
        $balance = $wallet->getBalanceAttribute();

        // Get the decimal places value from the wallet.
        $decimalPlacesValue = $wallet->decimal_places;

        // Use the formatter service to format the balance as a float.
        return app(FormatterServiceInterface::class)->floatValue(
        // The balance of the wallet.
            $balance,

            // The number of decimal places for the wallet.
            $decimalPlacesValue,
        );
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
