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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\RecordsNotFoundException;

interface Wallet
{
    /**
     * Deposit the specified amount of money into the wallet.
     *
     * @param int|string $amount The amount to deposit.
     * @param array<mixed>|null $meta Additional information for the transaction.
     * @param bool $confirmed Whether the transaction is confirmed or not.
     * @return Transaction The created transaction.
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function deposit(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * Withdraw the specified amount of money from the wallet.
     *
     * @param int|string $amount The amount to withdraw.
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
    public function withdraw(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * Forced to withdraw funds from the wallet.
     *
     * @param int|string $amount The amount to withdraw.
     * @param array<mixed>|null $meta Additional information for the transaction.
     * @param bool $confirmed Whether the transaction is confirmed or not.
     * @return Transaction The created transaction.
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function forceWithdraw(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * Transfer funds from this wallet to another.
     *
     * @param self $wallet The wallet to transfer funds to.
     * @param int|string $amount The amount to transfer.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional information for the transaction.
     * @return Transfer The created transaction.
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws BalanceIsEmpty If the balance is empty.
     * @throws InsufficientFunds If the amount exceeds the balance.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function transfer(self $wallet, int|string $amount, ExtraDtoInterface|array|null $meta = null): Transfer;

    /**
     * Safely transfers funds from this wallet to another.
     *
     * This method attempts to transfer funds from this wallet to another wallet.
     * If an error occurs during the process, null is returned.
     *
     * @param self $wallet The wallet to transfer funds to.
     * @param int|string $amount The amount to transfer.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional information for the transaction.
     *                                                This can be an instance of an ExtraDtoInterface
     *                                                or an array of arbitrary data.
     * @return null|Transfer The created transaction, or null if an error occurred.
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws BalanceIsEmpty If the balance is empty.
     * @throws InsufficientFunds If the amount exceeds the balance.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function safeTransfer(
        self $wallet,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): ?Transfer;

    /**
     * Forces a transfer of funds from this wallet to another, bypassing certain safety checks.
     *
     * This method is intended for use in scenarios where a transfer must be completed regardless of
     * the usual validation checks (e.g., sufficient funds, wallet status). It is critical to use this
     * method with caution as it can result in negative balances or other unintended consequences.
     *
     * @param self $wallet The wallet instance to which funds will be transferred.
     * @param int|string $amount The amount of funds to transfer. Can be specified as an integer or a string.
     * @param ExtraDtoInterface|array<mixed>|null $meta Additional metadata associated with the transfer. This
     * can be used to store extra information about the transaction, such as reasons for the transfer or
     * identifiers linking to other systems.
     * @return Transfer Returns a Transfer object representing the completed transaction.
     *
     * @throws AmountInvalid If the amount specified is invalid (e.g., negative values).
     * @throws RecordsNotFoundException If the target wallet cannot be found.
     * @throws TransactionFailedException It indicates that the transfer could not be completed due to a failure
     * in the underlying transaction system.
     * @throws ExceptionInterface A generic exception interface catch-all for any other exceptions that
     * might occur during the execution of the transfer.
     */
    public function forceTransfer(
        self $wallet,
        int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): Transfer;

    /**
     * Checks if the wallet can safely withdraw the specified amount.
     *
     * @param int|string $amount The amount to withdraw.
     * @param bool $allowZero Whether to allow withdrawing when the balance is zero.
     * @return bool Returns true if the wallet can withdraw the specified amount, false otherwise.
     */
    public function canWithdraw(int|string $amount, bool $allowZero = false): bool;

    /**
     * Returns the balance of the wallet as a string.
     *
     * The balance is the total amount of funds held by the wallet.
     *
     * @return non-empty-string The balance of the wallet.
     */
    public function getBalanceAttribute(): string;

    /**
     * Returns the balance of the wallet as an integer.
     *
     * @return int The balance of the wallet. This value is the result of
     *             {@see getBalanceAttribute()} converted to an integer.
     */
    public function getBalanceIntAttribute(): int;

/**
 * Represents a relationship where a wallet has many transactions.
 *
 * @return HasMany<Transaction> A collection of transactions associated with this wallet.
 */
public function walletTransactions(): HasMany;

    /**
     * Returns all the transactions associated with this wallet.
     *
     * This method returns a morph many relationship that represents all the transactions
     * associated with this wallet. The transactions may be of different types, such as
     * deposits, withdrawals, or transfers.
     *
     * @return MorphMany<Transaction> A collection of transactions associated with this wallet.
     */
    public function transactions(): MorphMany;

    /**
     * Returns all the transfers sent by this wallet.
     *
     * @return HasMany<Transfer> A collection of transfers sent by this wallet.
     */
    public function transfers(): HasMany;

    /**
     * Returns all the transfers received by this wallet.
     *
     * @return HasMany<Transfer> A collection of transfers received by this wallet.
     */
    public function receivedTransfers(): HasMany;
}
