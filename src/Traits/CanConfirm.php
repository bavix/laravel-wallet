<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\ConfirmedInvalid;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\UnconfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Service\TranslatorServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\CommonServiceLegacy;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\LockServiceLegacy;

trait CanConfirm
{
    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws ExceptionInterface
     */
    public function confirm(Transaction $transaction): bool
    {
        return app(DatabaseServiceInterface::class)->transaction(function () use ($transaction) {
            if ($transaction->type === Transaction::TYPE_WITHDRAW) {
                app(ConsistencyServiceInterface::class)->checkPotential(
                    app(CastServiceInterface::class)->getWallet($this),
                    app(MathServiceInterface::class)->abs($transaction->amount)
                );
            }

            return $this->forceConfirm($transaction);
        });
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
     */
    public function resetConfirm(Transaction $transaction): bool
    {
        return app(LockServiceLegacy::class)->lock($this, function () use ($transaction) {
            return app(DatabaseServiceInterface::class)->transaction(function () use ($transaction) {
                if (!$transaction->confirmed) {
                    throw new UnconfirmedInvalid(
                        app(TranslatorServiceInterface::class)->get('wallet::errors.unconfirmed_invalid'),
                        ExceptionInterface::UNCONFIRMED_INVALID
                    );
                }

                $wallet = app(CastServiceInterface::class)->getWallet($this);
                $mathService = app(MathServiceInterface::class);
                $negativeAmount = $mathService->negative($transaction->amount);

                return $transaction->update(['confirmed' => false]) &&
                    // update balance
                    app(CommonServiceLegacy::class)
                        ->addBalance($wallet, $negativeAmount)
                    ;
            });
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
     */
    public function forceConfirm(Transaction $transaction): bool
    {
        return app(LockServiceLegacy::class)->lock($this, function () use ($transaction) {
            return app(DatabaseServiceInterface::class)->transaction(function () use ($transaction) {
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

                return $transaction->update(['confirmed' => true]) &&
                    // update balance
                    app(CommonServiceLegacy::class)
                        ->addBalance($wallet, $transaction->amount)
                    ;
            });
        });
    }
}
