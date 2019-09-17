<?php

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Services\ProxyService;
use Bavix\Wallet\Services\WalletService;

class MemoryStore implements Storable
{

    /**
     * @inheritDoc
     */
    public function getBalance($object): int
    {
        $wallet = app(WalletService::class)->getWallet($object);
        $proxy = app(ProxyService::class);
        if (!$proxy->has($wallet->getKey())) {
            $proxy->set($wallet->getKey(), (int) $wallet->getOriginal('balance', 0));
        }

        return $proxy[$wallet->getKey()];
    }

    /**
     * @inheritDoc
     */
    public function incBalance($object, int $amount): int
    {
        $wallet = app(WalletService::class)->getWallet($object);
        $proxy = app(ProxyService::class);
        $balance = $wallet->balance + $amount;

        if ($proxy->has($wallet->getKey())) {
            $balance = $proxy->get($wallet->getKey()) + $amount;
        }

        $this->setBalance($object, $balance);
        return $balance;
    }

    /**
     * @inheritDoc
     */
    public function setBalance($object, int $amount): bool
    {
        $wallet = app(WalletService::class)->getWallet($object);
        $proxy = app(ProxyService::class);
        return (bool)$proxy->set($wallet->getKey(), $amount);
    }

}
