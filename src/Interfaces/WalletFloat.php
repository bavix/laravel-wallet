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
     * @param float|int|non-empty-string $amount
     * @param null|array<mixed> $meta
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
     * @param float|int|non-empty-string $amount
     * @param array<mixed>|null $meta
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
     * @param float|int|non-empty-string $amount
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
    ): Transaction;

    /**
     * @param float|int|non-empty-string $amount
     * @param ExtraDtoInterface|array<mixed>|null $meta
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
     * @param float|int|non-empty-string $amount
     * @param ExtraDtoInterface|array<mixed>|null $meta
     *
     * @throws AmountInvalid If the amount is invalid.
     */
    public function safeTransferFloat(
        Wallet $wallet,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): ?Transfer;

    /**
     * @param float|int|non-empty-string $amount
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
    ): Transfer;

    /**
     * @param float|int|non-empty-string $amount
     *
     * @throws AmountInvalid
     */
    public function canWithdrawFloat(float|int|string $amount): bool;

    /**
     * @return non-empty-string
     */
    public function getBalanceFloatAttribute(): string;

    public function getBalanceFloatNumAttribute(): float;
}
