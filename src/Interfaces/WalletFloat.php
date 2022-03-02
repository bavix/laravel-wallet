<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\RecordsNotFoundException;

interface WalletFloat
{
    /**
     * @param float|string $amount
     *
     * @throws AmountInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function depositFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction;

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
    public function withdrawFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction;

    /**
     * @param float|string $amount
     *
     * @throws AmountInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceWithdrawFloat($amount, ?array $meta = null, bool $confirmed = true): Transaction;

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
    public function transferFloat(Wallet $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param float|string $amount
     */
    public function safeTransferFloat(Wallet $wallet, $amount, ?array $meta = null): ?Transfer;

    /**
     * @param float|string $amount
     *
     * @throws AmountInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceTransferFloat(Wallet $wallet, $amount, ?array $meta = null): Transfer;

    /**
     * @param float|string $amount
     */
    public function canWithdrawFloat($amount): bool;

    /**
     * @return float|int|string
     */
    public function getBalanceFloatAttribute();
}
