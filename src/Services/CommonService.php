<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Objects\Operation;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class CommonService
{

    /**
     * @param Wallet $self
     * @param Operation[] $transactions
     * @return Transaction[]
     */
    public function enforce(Wallet $self, array $transactions): array
    {
        $objects = [];
        $amount = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->isConfirmed()) {
                $amount += $transaction->getAmount();
            }

            $objects[] = $transaction->create($self);
        }

        $this->addBalance($self, $amount);
        return $objects;
    }


    /**
     * this method adds a new transfer to the transfer table
     *
     * @param Wallet $wallet
     * @param Transaction $withdraw
     * @param Transaction $deposit
     * @param string $status
     * @return Transfer
     * @throws
     */

    /**
     * @param Bring[] $brings
     * @return array
     * @throws
     */
    public function assemble(array $brings): array
    {
        $objects = [];
        foreach ($brings as $bring) {
            $objects[] = $bring->create();
        }

        return $objects;
    }


    /**
     * @param Wallet $wallet
     * @param int $amount
     * @return bool
     * @throws
     */
    public function addBalance(Wallet $wallet, int $amount): bool
    {
        /**
         * @var ProxyService $proxy
         * @var \Bavix\Wallet\Models\Wallet $wallet
         */
        $proxy = \app(ProxyService::class);
        $balance = $wallet->balance;
        if ($proxy->has($wallet->getKey())) {
            $balance = $proxy->get($wallet->getKey());
        }

        $balance += $amount;
        if ($wallet->update(\compact('balance'))) {
            $proxy->set($wallet->getKey(), $balance);
            return true;
        }

        return false;
    }

}
