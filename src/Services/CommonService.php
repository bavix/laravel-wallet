<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Objects\Operation;

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
