<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\ConfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\WalletService;
use Illuminate\Support\Facades\DB;

trait CanConfirm
{


    /**
     * @param Transaction $transaction
     * @return bool
     */
    public function confirm(Transaction $transaction): bool
    {
        return DB::transaction(function() use ($transaction) {
            $wallet = app(WalletService::class)
                ->getWallet($this);

            if (!$wallet->refreshBalance()) {
                // @codeCoverageIgnoreStart
                return false;
                // @codeCoverageIgnoreEnd
            }

            if ($transaction->type === Transaction::TYPE_WITHDRAW) {
                app(CommonService::class)->verifyWithdraw(
                    $wallet,
                    \abs($transaction->amount)
                );
            }

            return $this->forceConfirm($transaction);
        });
    }

    /**
     * @param Transaction $transaction
     * @return bool
     */
    public function safeConfirm(Transaction $transaction): bool
    {
        try {
            return $this->confirm($transaction);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param Transaction $transaction
     * @return bool
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     */
    public function forceConfirm(Transaction $transaction): bool
    {
        return DB::transaction(function() use ($transaction) {

            $wallet = app(WalletService::class)
                ->getWallet($this);

            if ($transaction->confirmed) {
                throw new ConfirmedInvalid(); // todo
            }

            if ($wallet->id !== $transaction->wallet_id) {
                throw new WalletOwnerInvalid(); // todo
            }

            return $transaction->update(['confirmed' => true]) &&

                // update balance
                app(CommonService::class)
                    ->addBalance($wallet, $transaction->amount);

        });
    }

}
