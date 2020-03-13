<?php

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Mathable;
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
    public function getBalance($object)
    {
        $wallet = app(WalletService::class)->getWallet($object);
        if (!\array_key_exists($wallet->getKey(), $this->balanceSheets)) {
            $balance = method_exists($wallet, 'getRawOriginal') ?
                $wallet->getRawOriginal('balance', 0) : $wallet->getOriginal('balance', 0);

            $this->balanceSheets[$wallet->getKey()] = app(Mathable::class)->round($balance);
        }

        return $this->balanceSheets[$wallet->getKey()];
    }

    /**
     * @inheritDoc
     */
    public function incBalance($object, $amount)
    {
        $math = app(Mathable::class);
        $balance = $math->add($this->getBalance($object), $amount);
        $this->setBalance($object, $balance);
        return $balance;
    }

    /**
     * @inheritDoc
     */
    public function setBalance($object, $amount): bool
    {
        $wallet = app(WalletService::class)->getWallet($object);
        $this->balanceSheets[$wallet->getKey()] = app(Mathable::class)->round($amount ?: 0);
        return true;
    }

}
