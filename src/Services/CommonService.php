<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Objects\Operation;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Support\Facades\DB;
use function app;
use function compact;

class CommonService
{

    /**
     * @param Wallet $wallet
     * @param int $amount
     * @return void
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function verifyWithdraw(Wallet $wallet, int $amount): void
    {
        /**
         * @var HasWallet $wallet
         */
        if ($amount && !$wallet->balance) {
            throw new BalanceIsEmpty(trans('wallet::errors.wallet_empty'));
        }

        if (!$wallet->canWithdraw($amount)) {
            throw new InsufficientFunds(trans('wallet::errors.insufficient_funds'));
        }
    }

    /**
     * Create Operation with DB::transaction
     *
     * @param Wallet $self
     * @param Operation[] $transactions
     * @return Transaction[]
     */
    public function enforce(Wallet $self, array $transactions): array
    {
        return DB::transaction(function () use ($self, $transactions) {
            return $this->multiOperation($self, $transactions);
        });
    }

    /**
     * Create Operation without DB::transaction
     *
     * @param Wallet $self
     * @param array $operations
     * @return array
     */
    public function multiOperation(Wallet $self, array $operations): array
    {
        $amount = 0;
        $objects = [];
        foreach ($operations as $operation) {
            if ($operation->isConfirmed()) {
                $amount += $operation->getAmount();
            }

            $objects[] = $operation
                ->setWallet($self)
                ->create();
        }

        $this->addBalance($self, $amount);
        return $objects;
    }

    /**
     * Create Bring with DB::transaction
     *
     * @param Bring[] $brings
     * @return array
     * @throws
     */
    public function assemble(array $brings): array
    {
        return DB::transaction(function () use ($brings) {
            return $this->multiBrings($brings);
        });
    }

    /**
     * Create Bring without DB::transaction
     *
     * @param array $brings
     * @return array
     */
    public function multiBrings(array $brings): array
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
