<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\RecordsNotFoundException;

interface WalletFloat
{
    /**
     * Deposit a float amount of money into the wallet.
     *
     * @param float|int|non-empty-string $amount The amount to deposit.
     * @param null|array<mixed> $meta Additional information for the transaction.
     * @param bool $confirmed Whether the transaction is confirmed or not.
     * @return Transaction The created transaction.
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function depositFloat(
        float|int|string $amount,
        ?array $meta = null,
        bool $confirmed = true
    ): Transaction;

    /**
     * Withdraw the specified float amount of money from the wallet.
     *
     * @param float|int|non-empty-string $amount The amount to withdraw.
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
    public function withdrawFloat(
        float|int|string $amount,
        ?array $meta = null,
        bool $confirmed = true
    ): Transaction;

    /**
     * Forced to withdraw funds from the wallet.
     *
     * @param float|int|non-empty-string $amount The amount to withdraw.
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
    ): Transaction;

    /**
     * Transfer funds from this wallet to another.
     *
     * @param Wallet $wallet The wallet to transfer funds to.
     * @param float|int|non-empty-string $amount The amount to transfer.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional information for the transaction.
     *                                                This can be an instance of an ExtraDtoInterface
     *                                                or an array of arbitrary data.
     * @return Transfer The created transaction.
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws BalanceIsEmpty If the balance is empty.
     * @throws InsufficientFunds If the amount exceeds the balance.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function transferFloat(
        Wallet $wallet,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): Transfer;

    /**
     * Safely transfers funds from this wallet to another.
     *
     * This method will not throw an exception if the transfer fails. Instead, it will return null.
     *
     * @param Wallet $wallet The wallet to transfer funds to.
     * @param float|int|non-empty-string $amount The amount to transfer.
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
    ): ?Transfer;

    /**
     * Forces a transfer of funds from this wallet to another, bypassing certain safety checks.
     *
     * This method is intended for use in scenarios where a transfer must be completed regardless of
     * the usual validation checks (e.g., sufficient funds, wallet status). It is critical to use this
     * method with caution as it can result in negative balances or other unintended consequences.
     *
     * @param Wallet $wallet The wallet instance to which funds will be transferred.
     * @param float|int|non-empty-string $amount The amount of funds to transfer. Can be specified as a float, int, or string.
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
    ): Transfer;

    /**
     * Checks if the user can withdraw the specified amount of funds.
     *
     * @param float|int|non-empty-string $amount The amount of funds to withdraw. Can be specified as a float, int, or string.
     * @return bool Returns TRUE if the withdrawal is possible, FALSE otherwise.
     *
     * @throws AmountInvalid If the amount is invalid (e.g., negative values).
     */
    public function canWithdrawFloat(float|int|string $amount): bool;

    /**
     * Returns the user's current balance as a string value.
     *
     * This method returns the balance of the wallet as a string. The balance is the total amount of funds
     * held by the wallet.
     *
     * @return non-empty-string The user's current balance as a string (e.g. "1.23").
     */
    public function getBalanceFloatAttribute(): string;

    /**
     * Returns the user's current balance as a float value.
     *
     * The float value is the actual value of the balance, which may not be
     * the same as the value stored in the database. This method is useful
     * when you need to perform calculations or formatting on the balance.
     *
     * @return float The user's current balance as a float (e.g. 1.23).
     */
    public function getBalanceFloatNumAttribute(): float;
}
