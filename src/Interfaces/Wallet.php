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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\RecordsNotFoundException;

interface Wallet
{
    /**
     * @param int|non-empty-string $amount
     * @param array<mixed>|null $meta
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function deposit(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int|non-empty-string $amount
     * @param array<mixed>|null $meta
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
     * @param int|non-empty-string $amount
     * @param array<mixed>|null $meta
     *
     * @throws AmountInvalid If the amount is invalid.
     * @throws RecordsNotFoundException If the wallet is not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function forceWithdraw(int|string $amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param int|non-empty-string $amount
     * @param ExtraDtoInterface|array<mixed>|null $meta
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
     * @param int|non-empty-string $amount
     * @param ExtraDtoInterface|array<mixed>|null $meta
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
     * @param int|non-empty-string $amount
     * @param ExtraDtoInterface|array<mixed>|null $meta
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
     * @param int|non-empty-string $amount
     */
    public function canWithdraw(int|string $amount, bool $allowZero = false): bool;

    /**
     * @return non-empty-string
     */
    public function getBalanceAttribute(): string;

    public function getBalanceIntAttribute(): int;

    /**
     * @return HasMany<Transaction, self>
     */
    public function walletTransactions(): HasMany;

    /**
     * @return MorphMany<Transaction, Model>
     */
    public function transactions(): MorphMany;

    /**
     * @return HasMany<Transfer, self>
     */
    public function transfers(): HasMany;

    /**
     * @return HasMany<Transfer, self>
     */
    public function receivedTransfers(): HasMany;
}
