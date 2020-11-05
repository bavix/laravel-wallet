<?php

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Services\WalletService;

class Store implements Storable
{
    /**
     * @var string[]
     */
    protected $balanceSheets = [];

    /**
     * {@inheritdoc}
     */
    public function getBalance($object)
    {
        $wallet = app(WalletService::class)->getWallet($object);
        if (! \array_key_exists($wallet->getKey(), $this->balanceSheets)) {
            $balance = method_exists($wallet, 'getRawOriginal') ?
                $wallet->getRawOriginal('balance', 0) : $wallet->getOriginal('balance', 0);

            $this->balanceSheets[$wallet->getKey()] = $this->round($balance);
        }

        return $this->balanceSheets[$wallet->getKey()];
    }

    /**
     * {@inheritdoc}
     */
    public function incBalance($object, $amount): string
    {
        $math = app(Mathable::class);
        $balance = $math->add($this->getBalance($object), $amount);
        $balance = $this->round($balance);
        $this->setBalance($object, $balance);

        return $balance;
    }

    /**
     * {@inheritdoc}
     */
    public function setBalance($object, $amount): bool
    {
        $wallet = app(WalletService::class)->getWallet($object);
        $this->balanceSheets[$wallet->getKey()] = $this->round($amount);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function fresh(): bool
    {
        $this->balanceSheets = [];

        return true;
    }

    /**
     * @param int|string $balance
     * @return string
     */
    protected function round($balance): string
    {
        return app(Mathable::class)->round($balance ?: 0);
    }
}
