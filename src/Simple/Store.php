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

            $this->balanceSheets[$wallet->getKey()] = $this->toInt($balance);
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
        $balance = $this->toInt($balance);
        $this->setBalance($object, $balance);
        return $balance;
    }

    /**
     * @inheritDoc
     */
    public function setBalance($object, $amount): bool
    {
        $wallet = app(WalletService::class)->getWallet($object);
        $this->balanceSheets[$wallet->getKey()] = $this->toInt($amount);
        return true;
    }

    /**
     * @param string $balance
     * @return string
     */
    protected function toInt($balance): string
    {
        return app(Mathable::class)->round($balance ?: 0);
    }

}
