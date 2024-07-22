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
     * Confirm the transaction.
     *
     * This method confirms the given transaction if it is not already confirmed.
     *
     * @param Transaction $transaction The transaction to confirm.
     * @return bool Returns true if the transaction was confirmed, false otherwise.
     *
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
     * Safely confirms the transaction.
     *
     * This method attempts to confirm the given transaction. If an exception occurs during the confirmation process,
     * it will be caught and handled. If the confirmation is successful, true will be returned. If an exception occurs,
     * false will be returned.
     *
     * @param Transaction $transaction The transaction to confirm.
     * @return bool Returns true if the transaction was confirmed, false otherwise.
     *
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
     * Reset the confirmation of the transaction.
     *
     * This method is used to remove the confirmation from a transaction.
     * If the transaction is already confirmed, a `ConfirmedInvalid` exception will be thrown.
     * If the transaction does not belong to the wallet, a `WalletOwnerInvalid` exception will be thrown.
     * If the transaction was not found, a `RecordNotFoundException` will be thrown.
     *
     * @param Transaction $transaction The transaction to reset.
     * @return bool Returns true if the confirmation was reset, false otherwise.
     *
     * @throws UnconfirmedInvalid       If the transaction is not confirmed.
     * @throws WalletOwnerInvalid       If the transaction does not belong to the wallet.
     * @throws RecordNotFoundException  If the transaction was not found.
     * @throws RecordsNotFoundException If no transactions were found.
     * @throws TransactionFailedException If the transaction failed.
     * @throws ExceptionInterface        If an exception occurred.
     */
    public function resetConfirm(Transaction $transaction): bool;

    /**
     * Safely reset the confirmation of the transaction.
     *
     * This method is used to remove the confirmation from a transaction.
     * If the transaction is already confirmed, the confirmation will be reset.
     * If the transaction does not belong to the wallet, a `WalletOwnerInvalid` exception will be thrown.
     * If the transaction was not found, a `RecordNotFoundException` will be thrown.
     *
     * @param Transaction $transaction The transaction to reset.
     * @return bool Returns true if the confirmation was reset, false otherwise.
     */
    public function safeResetConfirm(Transaction $transaction): bool;

    /**
     * Force confirm the transaction.
     *
     * This method forces the confirmation of the given transaction even if it is already confirmed.
     * If the transaction is already confirmed, a `ConfirmedInvalid` exception will be thrown.
     * If the transaction does not belong to the wallet, a `WalletOwnerInvalid` exception will be thrown.
     * If the transaction was not found, a `RecordNotFoundException` will be thrown.
     *
     * @param Transaction $transaction The transaction to confirm.
     * @return bool Returns true if the transaction was confirmed, false otherwise.
     *
     * @throws ConfirmedInvalid         If the transaction is already confirmed.
     * @throws WalletOwnerInvalid       If the transaction does not belong to the wallet.
     * @throws RecordNotFoundException If the transaction was not found.
     * @throws RecordsNotFoundException If no transactions were found.
     * @throws TransactionFailedException If the transaction failed.
     * @throws ExceptionInterface       If an exception occurred.
     */
    public function forceConfirm(Transaction $transaction): bool;
}
