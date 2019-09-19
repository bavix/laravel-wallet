<?php

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Services\WalletService;

class Store implements Storable
{

    /**
     * @var array
     */
    protected $balanceSheets = [];

    /**
     * @inheritDoc
     */
    public function getBalance($object): int
    {
        $wallet = app(WalletService::class)->getWallet($object);
        if (!\array_key_exists($wallet->getKey(), $this->balanceSheets)) {
            $this->balanceSheets[$wallet->getKey()] = (int)$wallet->getOriginal('balance', 0);
        }

        return $this->balanceSheets[$wallet->getKey()];
    }

    /**
     * @inheritDoc
     */
    public function incBalance($object, int $amount): int
    {
        $balance = $this->getBalance($object) + $amount;
        $this->setBalance($object, $balance);
        return $balance;
    }

    /**
     * @inheritDoc
     */
    public function setBalance($object, int $amount): bool
    {
        $wallet = app(WalletService::class)->getWallet($object);
        $this->balanceSheets[$wallet->getKey()] = $amount;
        return true;
    }

}
