<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\ConfirmedInvalid;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\UnconfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transaction;
use Illuminate\Database\RecordsNotFoundException;

interface Confirmable
{
    /**
     * @throws BalanceIsEmpty          If the balance is empty.
     * @throws InsufficientFunds       If there are insufficient funds.
     * @throws ConfirmedInvalid         If the transaction is already confirmed.
     * @throws WalletOwnerInvalid      If the transaction does not belong to the wallet.
     * @throws RecordNotFoundException If the transaction was not found.
     * @throws RecordsNotFoundException If no transactions were found.
     * @throws TransactionFailedException If the transaction failed.
     * @throws ExceptionInterface       If an exception occurred.
     */
    public function confirm(Transaction $transaction): bool;

    /**
     * @throws BalanceIsEmpty          If the balance is empty.
     * @throws InsufficientFunds       If there are insufficient funds.
     * @throws ConfirmedInvalid         If the transaction is already confirmed.
     * @throws WalletOwnerInvalid      If the transaction does not belong to the wallet.
     * @throws RecordNotFoundException If the transaction was not found.
     * @throws RecordsNotFoundException If no transactions were found.
     * @throws TransactionFailedException If the transaction failed.
     * @throws ExceptionInterface       If an exception occurred.
     */
    public function safeConfirm(Transaction $transaction): bool;

    /**
     * @throws UnconfirmedInvalid       If the transaction is not confirmed.
     * @throws WalletOwnerInvalid       If the transaction does not belong to the wallet.
     * @throws RecordNotFoundException  If the transaction was not found.
     * @throws RecordsNotFoundException If no transactions were found.
     * @throws TransactionFailedException If the transaction failed.
     * @throws ExceptionInterface        If an exception occurred.
     */
    public function resetConfirm(Transaction $transaction): bool;

    public function safeResetConfirm(Transaction $transaction): bool;

    /**
     * @throws ConfirmedInvalid         If the transaction is already confirmed.
     * @throws WalletOwnerInvalid       If the transaction does not belong to the wallet.
     * @throws RecordNotFoundException If the transaction was not found.
     * @throws RecordsNotFoundException If no transactions were found.
     * @throws TransactionFailedException If the transaction failed.
     * @throws ExceptionInterface       If an exception occurred.
     */
    public function forceConfirm(Transaction $transaction): bool;
}
