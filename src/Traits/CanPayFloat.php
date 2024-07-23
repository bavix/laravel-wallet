<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 *
 * @psalm-require-implements \Bavix\Wallet\Interfaces\Customer
 */
trait CanPayFloat
{
    /**
     * Trait `HasWalletFloat` conflicts with `CanPay` methods.
     * We need to exclude these methods from `HasWalletFloat`.
     *
     * @see HasWalletFloat
     * @see CanPay
     *
     * The methods are "excluded" using the `insteadof` keyword.
     * This allows us to use the `CanPay` trait without causing conflicts.
     */
    use HasWalletFloat, CanPay {
        /**
         * Deposit a float amount of money into the wallet.
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
        CanPay::deposit insteadof HasWalletFloat;

        /**
         * Withdraw the specified float amount of money from the wallet.
         *
         * @param float|int|string $amount The amount to withdraw.
         * @param array<mixed>|null $meta Additional information for the transaction.
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
        CanPay::withdraw insteadof HasWalletFloat;

        /**
         * Check if the wallet can be withdrawn.
         *
         * @param float|int|string $amount The amount to check.
         * @return bool Whether the wallet can be withdrawn or not.
         */
        CanPay::canWithdraw insteadof HasWalletFloat;

        /**
         * Forced to withdraw funds from the wallet.
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
        CanPay::forceWithdraw insteadof HasWalletFloat;

        /**
         * Transfer the specified float amount of money from the wallet to a customer.
         *
         * @param Customer $customer The customer to transfer to.
         * @param float|int|string $amount The amount to transfer.
         * @param null|array<mixed> $meta Additional information for the transaction.
         * @return Transfer The created transfer.
         *
         * @throws AmountInvalid If the amount is invalid.
         * @throws BalanceIsEmpty If the balance is empty.
         * @throws InsufficientFunds If the amount exceeds the balance.
         * @throws RecordsNotFoundException If the wallet is not found.
         * @throws TransactionFailedException If the transaction fails.
         * @throws ExceptionInterface If an exception occurs.
         */
        CanPay::transfer insteadof HasWalletFloat;

        /**
         * Try to transfer the specified float amount of money from the wallet to a customer.
         *
         * @param Customer $customer The customer to transfer to.
         * @param float|int|string $amount The amount to transfer.
         * @param null|array<mixed> $meta Additional information for the transaction.
         * @return Transfer|null The created transfer or null if the transfer fails.
         */
        CanPay::safeTransfer insteadof HasWalletFloat;

        /**
         * Forced to transfer the specified float amount of money from the wallet to a customer.
         *
         * @param Customer $customer The customer to transfer to.
         * @param float|int|string $amount The amount to transfer.
         * @param null|array<mixed> $meta Additional information for the transaction.
         * @return Transfer The created transfer.
         *
         * @throws AmountInvalid If the amount is invalid.
         * @throws RecordsNotFoundException If the wallet is not found.
         * @throws TransactionFailedException If the transaction fails.
         * @throws ExceptionInterface If an exception occurs.
         */
        CanPay::forceTransfer insteadof HasWalletFloat;

        /**
         * Get the transactions of the wallet.
         *
         * @return MorphMany The transactions of the wallet.
         */
        CanPay::transactions insteadof HasWalletFloat;

        /**
         * Get the transfers of the wallet.
         *
         * @return MorphMany The transfers of the wallet.
         */
        CanPay::transfers insteadof HasWalletFloat;

        /**
         * Get or set the wallet.
         *
         * @param Wallet|null $wallet The wallet to set or get.
         * @return Wallet The wallet.
         */
        CanPay::wallet insteadof HasWalletFloat;

        /**
         * Get the balance of the wallet as a float.
         *
         * @return float The balance of the wallet.
         */
        CanPay::getBalanceAttribute insteadof HasWalletFloat;
    }
}
