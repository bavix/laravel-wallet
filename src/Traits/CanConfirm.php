<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Enums\TransactionType;
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
     * Confirms an unconfirmed transaction.
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
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
            if ($transaction->type === TransactionType::Withdraw) {
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
     * Confirms transaction and returns false on non-critical failures.
     *
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function safeConfirm(Transaction $transaction): bool
    {
        try {
            // Attempt to confirm the transaction
            return $this->confirm($transaction);
        } catch (ConfirmedInvalid) {
            // If the transaction is already confirmed, return true
            return true;
        } catch (ExceptionInterface) {
            // If an exception occurred, return false
            return false;
        }
    }

    /**
     * Resets confirmation flag for a confirmed transaction.
     *
     * @throws UnconfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
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

    public function safeResetConfirm(Transaction $transaction): bool
    {
        try {
            // Attempt to reset the confirmation of the transaction
            return $this->resetConfirm($transaction);
        } catch (UnconfirmedInvalid) {
            // If the transaction is not confirmed, simply return true
            return true;
        } catch (ExceptionInterface) {
            // If an exception occurs, return false
            return false;
        }
    }

    /**
     * Confirms transaction without pre-checking transaction type.
     *
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
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
