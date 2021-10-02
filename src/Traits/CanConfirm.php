<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\ConfirmedInvalid;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\UnconfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Interfaces\Confirmable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Services\LockService;
use Bavix\Wallet\Services\WalletService;

trait CanConfirm
{
    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     */
    public function confirm(Transaction $transaction): bool
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($transaction) {
            /** @var Confirmable|Wallet $self */
            $self = $this;

            return app(DbService::class)->transaction(static function () use ($self, $transaction) {
                $wallet = app(WalletService::class)->getWallet($self);
                if (!$wallet->refreshBalance()) {
                    return false;
                }

                if ($transaction->type === Transaction::TYPE_WITHDRAW) {
                    app(ConsistencyInterface::class)->checkPotential(
                        $wallet,
                        app(MathInterface::class)->abs($transaction->amount)
                    );
                }

                return $self->forceConfirm($transaction);
            });
        });
    }

    public function safeConfirm(Transaction $transaction): bool
    {
        try {
            return $this->confirm($transaction);
        } catch (\Throwable $throwable) {
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
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($transaction) {
            /** @var Wallet $self */
            $self = $this;

            return app(DbService::class)->transaction(static function () use ($self, $transaction) {
                $wallet = app(WalletService::class)->getWallet($self);
                if (!$wallet->refreshBalance()) {
                    return false;
                }

                if (!$transaction->confirmed) {
                    throw new UnconfirmedInvalid(trans('wallet::errors.unconfirmed_invalid'));
                }

                $mathService = app(MathInterface::class);
                $negativeAmount = $mathService->negative($transaction->amount);

                return $transaction->update(['confirmed' => false]) &&
                    // update balance
                    app(CommonService::class)
                        ->addBalance($wallet, $negativeAmount)
                    ;
            });
        });
    }

    public function safeResetConfirm(Transaction $transaction): bool
    {
        try {
            return $this->resetConfirm($transaction);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     */
    public function forceConfirm(Transaction $transaction): bool
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($transaction) {
            /** @var Wallet $self */
            $self = $this;

            return app(DbService::class)->transaction(static function () use ($self, $transaction) {
                $wallet = app(WalletService::class)
                    ->getWallet($self)
                ;

                if ($transaction->confirmed) {
                    throw new ConfirmedInvalid(trans('wallet::errors.confirmed_invalid'));
                }

                if ($wallet->getKey() !== $transaction->wallet_id) {
                    throw new WalletOwnerInvalid(trans('wallet::errors.owner_invalid'));
                }

                return $transaction->update(['confirmed' => true]) &&
                    // update balance
                    app(CommonService::class)
                        ->addBalance($wallet, $transaction->amount)
                    ;
            });
        });
    }
}
