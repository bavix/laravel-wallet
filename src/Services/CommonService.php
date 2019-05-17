<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Objects\Transaction;
use Illuminate\Support\Facades\DB;

class CommonService
{

    /**
     * @param bool $dbTran
     * @param Wallet $self
     * @param Transaction[] $transactions
     * @return \Bavix\Wallet\Models\Transaction[]
     */
    public function enforce(bool $dbTran, Wallet $self, array $transactions): array
    {
        $callback = function () use ($self, $transactions) {
            $objects = [];

            foreach ($transactions as $transaction) {
                if ($transaction->isConfirmed()) {
                    $this->addBalance($self, $transaction->getAmount());
                }

                $objects[] = $transaction->create($self);
            }

            return $objects;
        };

        if ($dbTran) {
            return DB::transaction($callback);
        }

        return $callback();
    }

    /**
     * @param Wallet $wallet
     * @param int $amount
     * @return bool
     */
    public function addBalance(Wallet $wallet, int $amount): bool
    {
        $newBalance = $wallet->getBalanceAttribute() + $amount;
        $wallet->balance = $newBalance;

        if ($wallet->save()) {
            $proxy = app(ProxyService::class);
            $proxy->set($wallet->getKey(), $newBalance);
            return true;
        }

        return false;
    }

}
