<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Interfaces\Taxing;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Traits\HasWallet;
use function app;

class WalletService
{

    /**
     * Consider the fee that the system will receive.
     *
     * @param Wallet $wallet
     * @param int $amount
     * @return int
     */
    public function fee(Wallet $wallet, int $amount): int
    {
        if ($wallet instanceof Taxing) {
            return (int)($amount * $wallet->getFeePercent() / 100);
        }

        return 0;
    }

    /**
     * The amount of checks for errors
     *
     * @param int $amount
     * @throws
     */
    public function checkAmount(int $amount): void
    {
        if ($amount < 0) {
            throw new AmountInvalid(trans('wallet::errors.price_positive'));
        }
    }

    /**
     * @param Wallet $object
     * @return WalletModel
     */
    public function getWallet(Wallet $object): WalletModel
    {
        if ($object instanceof WalletModel) {
            return $object;
        }

        /**
         * @var HasWallet $object
         */
        return $object->wallet;
    }

    /**
     * @param Wallet $object
     * @return int
     */
    public function getBalance(Wallet $object): int
    {
        $wallet = $this->getWallet($object);
        $wallet->exists or $wallet->save();
        $proxy = app(ProxyService::class);
        if (!$proxy->has($wallet->getKey())) {
            $proxy->set($wallet->getKey(), (int)($wallet->attributes['balance'] ?? 0));
        }

        return $proxy[$wallet->getKey()];
    }

    /**
     * @param WalletModel $wallet
     * @return bool
     */
    public function refresh(WalletModel $wallet): bool
    {
        $balance = $wallet->getAvailableBalance();
        $wallet->balance = $balance;

        $proxy = app(ProxyService::class);
        $proxy->set($wallet->getKey(), $balance);

        return $wallet->save();
    }

}
