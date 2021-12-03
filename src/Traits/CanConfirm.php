<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\ConfirmedInvalid;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\UnconfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
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

trait CanConfirm
{
    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function confirm(Transaction $transaction): bool
    {
        if ($transaction->type === Transaction::TYPE_WITHDRAW) {
            app(ConsistencyServiceInterface::class)->checkPotential(
                app(CastServiceInterface::class)->getWallet($this),
                app(MathServiceInterface::class)->negative($transaction->amount)
            );
        }

        return $this->forceConfirm($transaction);
    }

    public function safeConfirm(Transaction $transaction): bool
    {
        try {
            return $this->confirm($transaction);
        } catch (ExceptionInterface $throwable) {
            return false;
        }
    }

    /**
     * Removal of confirmation (forced), use at your own peril and risk.
     *
     * @throws UnconfirmedInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function resetConfirm(Transaction $transaction): bool
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($transaction) {
            if (!$transaction->confirmed) {
                throw new UnconfirmedInvalid(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.unconfirmed_invalid'),
                    ExceptionInterface::UNCONFIRMED_INVALID
                );
            }

            $wallet = app(CastServiceInterface::class)->getWallet($this);
            app(RegulatorServiceInterface::class)->decrease($wallet, $transaction->amount);

            return $transaction->update(['confirmed' => false]);
        });
    }

    public function safeResetConfirm(Transaction $transaction): bool
    {
        try {
            return $this->resetConfirm($transaction);
        } catch (ExceptionInterface $throwable) {
            return false;
        }
    }

    /**
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceConfirm(Transaction $transaction): bool
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($transaction) {
            if ($transaction->confirmed) {
                throw new ConfirmedInvalid(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.confirmed_invalid'),
                    ExceptionInterface::CONFIRMED_INVALID
                );
            }

            $wallet = app(CastServiceInterface::class)->getWallet($this);
            if ($wallet->getKey() !== $transaction->wallet_id) {
                throw new WalletOwnerInvalid(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.owner_invalid'),
                    ExceptionInterface::WALLET_OWNER_INVALID
                );
            }

            app(RegulatorServiceInterface::class)->increase($wallet, $transaction->amount);

            return $transaction->update(['confirmed' => true]);
        });
    }
}
