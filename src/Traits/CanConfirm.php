<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\ConfirmedInvalid;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\UnconfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Service\TranslatorServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 */
trait CanConfirm
{
    /**
     * Confirm transaction.
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
    public function confirm(Transaction $transaction): bool
    {
        // Execute the confirmation process within an atomic block to ensure data consistency.
        return app(AtomicServiceInterface::class)->block($this, function () use ($transaction): bool {
            // Check if the transaction is already confirmed.
            // If it is, throw an exception.
            if ($transaction->confirmed) {
                // Why is there a check here without calling refresh? 
                // It's because this check can be performed in force confirm again.
                throw new ConfirmedInvalid(
                    // Get the error message from the translator service.
                    app(TranslatorServiceInterface::class)->get('wallet::errors.confirmed_invalid'),
                    // Set the error code to CONFIRMED_INVALID.
                    ExceptionInterface::CONFIRMED_INVALID
                );
            }

            // Check if the transaction type is withdrawal.
            if ($transaction->type === Transaction::TYPE_WITHDRAW) {
                // Check if the wallet has enough money to cover the withdrawal amount.
                app(ConsistencyServiceInterface::class)->checkPotential(
                    // Get the wallet.
                    app(CastServiceInterface::class)->getWallet($this),
                    // Negate the withdrawal amount to check for sufficient funds.
                    app(MathServiceInterface::class)->negative($transaction->amount)
                );
            }

            // Force confirm the transaction.
            return $this->forceConfirm($transaction);
        });
    }

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
    public function safeConfirm(Transaction $transaction): bool
    {
        try {
            // Attempt to confirm the transaction
            return $this->confirm($transaction);
        } catch (ConfirmedInvalid $e) {
            // If the transaction is already confirmed, return true
            return true;
        } catch (ExceptionInterface $e) {
            // If an exception occurred, return false
            return false;
        }
    }

    /**
     * Removal of confirmation (forced), use at your own peril and risk.
     *
     * This method is used to remove the confirmation from a transaction.
     * If the transaction is already confirmed, a `UnconfirmedInvalid` exception will be thrown.
     * If the transaction does not belong to the wallet, a `WalletOwnerInvalid` exception will be thrown.
     * If the transaction was not found, a `RecordNotFoundException` will be thrown.
     * If an exception occurred, a `TransactionFailedException` or `ExceptionInterface` will be thrown.
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
    public function resetConfirm(Transaction $transaction): bool
    {
        // Reset the confirmation of the transaction in a single database transaction
        return app(AtomicServiceInterface::class)->block($this, function () use ($transaction) {
            // Check if the transaction is already confirmed
            if (! $transaction->refresh()->confirmed) {
                throw new UnconfirmedInvalid(
                // If the transaction is not confirmed, throw an `UnconfirmedInvalid` exception
                    app(TranslatorServiceInterface::class)->get('wallet::errors.unconfirmed_invalid'),
                    // The code of the exception
                    ExceptionInterface::UNCONFIRMED_INVALID
                );
            }

            // Check if the transaction belongs to the wallet
            $wallet = app(CastServiceInterface::class)->getWallet($this);
            if ($wallet->getKey() !== $transaction->wallet_id) {
                throw new WalletOwnerInvalid(
                // If the transaction does not belong to the wallet, throw a `WalletOwnerInvalid` exception
                    app(TranslatorServiceInterface::class)->get('wallet::errors.owner_invalid'),
                    // The code of the exception
                    ExceptionInterface::WALLET_OWNER_INVALID
                );
            }

            // Decrease the amount of the wallet
            app(RegulatorServiceInterface::class)->decrease($wallet, $transaction->amount);

            // Reset the confirmation of the transaction
            return $transaction->update([
                'confirmed' => false,
            ]);
        });
    }

    /**
     * Safely reset the confirmation of the transaction.
     *
     * This method attempts to reset the confirmation of the given transaction. If an exception occurs during the
     * confirmation process, it will be caught and handled. If the confirmation is successfully reset, true will be
     * returned. If an exception occurs, false will be returned.
     *
     * @param Transaction $transaction The transaction to reset.
     * @return bool Returns true if the confirmation was reset, false otherwise.
     */
    public function safeResetConfirm(Transaction $transaction): bool
    {
        try {
            // Attempt to reset the confirmation of the transaction
            return $this->resetConfirm($transaction);
        } catch (UnconfirmedInvalid $e) {
            // If the transaction is not confirmed, simply return true
            return true;
        } catch (ExceptionInterface $e) {
            // If an exception occurs, return false
            return false;
        }
    }

    /**
     * Forces the confirmation of a transaction.
     *
     * This method attempts to confirm a transaction by decreasing the wallet's balance by the transaction's amount.
     * If the transaction is already confirmed or does not belong to the wallet, an exception will be thrown.
     * If the confirmation is successfully reset, true will be returned. If an exception occurs, false will be
     * returned.
     *
     * @param Transaction $transaction The transaction to confirm.
     * @return bool Returns true if the confirmation was reset, false otherwise.
     *
     * @throws ConfirmedInvalid If the transaction is already confirmed.
     * @throws WalletOwnerInvalid If the transaction does not belong to the wallet.
     * @throws RecordNotFoundException If the transaction was not found.
     * @throws RecordsNotFoundException If no transactions were found.
     * @throws TransactionFailedException If the transaction failed.
     * @throws ExceptionInterface If an exception occurred.
     */
    public function forceConfirm(Transaction $transaction): bool
    {
        // Attempt to confirm the transaction in a single database transaction
        return app(AtomicServiceInterface::class)->block($this, function () use ($transaction) {
            // Check if the transaction is already confirmed
            if ($transaction->refresh()->confirmed) {
                throw new ConfirmedInvalid(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.confirmed_invalid'),
                    ExceptionInterface::CONFIRMED_INVALID
                );
            }

            // Check if the transaction belongs to the wallet
            $wallet = app(CastServiceInterface::class)->getWallet($this);
            if ($wallet->getKey() !== $transaction->wallet_id) {
                throw new WalletOwnerInvalid(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.owner_invalid'),
                    ExceptionInterface::WALLET_OWNER_INVALID
                );
            }

            // Increase the balance of the wallet
            app(RegulatorServiceInterface::class)->increase($wallet, $transaction->amount);

            // Confirm the transaction
            return $transaction->update([
                'confirmed' => true,
            ]);
        });
    }
}
