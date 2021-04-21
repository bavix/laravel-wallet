<?php

declare(strict_types=1);

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Services\WalletService;

class Store implements Storable
{
    /** @var float[]|int[]|string[] */
    protected array $balanceSheets = [];

    public function getBalance($object)
    {
        $wallet = app(WalletService::class)->getWallet($object);
        if (!\array_key_exists($wallet->getKey(), $this->balanceSheets)) {
            $balance = method_exists($wallet, 'getRawOriginal') ?
                $wallet->getRawOriginal('balance', 0) : $wallet->getOriginal('balance', 0);

            $this->balanceSheets[$wallet->getKey()] = $this->round($balance);
        }

        return $this->balanceSheets[$wallet->getKey()];
    }

    public function incBalance($object, $amount): string
    {
        $math = app(Mathable::class);
        $balance = $math->add($this->getBalance($object), $amount);
        $balance = $this->round($balance);
        $this->setBalance($object, $balance);

        return $balance;
    }

    public function setBalance($object, $amount): bool
    {
        $wallet = app(WalletService::class)->getWallet($object);
        $this->balanceSheets[$wallet->getKey()] = $this->round($amount);

        return true;
    }

    public function fresh(): bool
    {
        $this->balanceSheets = [];

        return true;
    }

    protected function round($balance): string
    {
        return app(Mathable::class)->round((string) ($balance ?: 0));
    }
}
