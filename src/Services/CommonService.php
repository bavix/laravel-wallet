<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Objects\Operation;
use Illuminate\Support\Facades\DB;
use function app;
use function compact;

class CommonService
{

    /**
     * @param Wallet $self
     * @param Operation[] $transactions
     * @return Transaction[]
     */
    public function enforce(Wallet $self, array $transactions): array
    {
        return DB::transaction(function () use ($self, $transactions) {
            $amount = 0;
            $objects = [];
            foreach ($transactions as $transaction) {
                if ($transaction->isConfirmed()) {
                    $amount += $transaction->getAmount();
                }

                $objects[] = $transaction->create($self);
            }

            $this->addBalance($self, $amount);
            return $objects;
        });
    }

    /**
     * @param Bring[] $brings
     * @return array
     * @throws
     */
    public function assemble(array $brings): array
    {
        return DB::transaction(function () use ($brings) {
            $objects = [];
            foreach ($brings as $bring) {
                $objects[] = $bring->create();
            }

            return $objects;
        });
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
         * @var WalletModel $wallet
         */
        $proxy = app(ProxyService::class);
        $balance = $wallet->balance + $amount;
        if ($proxy->has($wallet->getKey())) {
            $balance = $proxy->get($wallet->getKey()) + $amount;
        }

        $result = $wallet->update(compact('balance'));
        $proxy->set($wallet->getKey(), $balance);

        return $result;
    }

}
