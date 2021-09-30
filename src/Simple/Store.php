<?php

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Internal\BookkeeperInterface;
use Bavix\Wallet\Internal\StorageInterface;
use Bavix\Wallet\Services\WalletService;

/**
 * @deprecated
 * @see BookkeeperInterface
 */
class Store implements Storable
{
    /**
     * {@inheritdoc}
     */
    public function getBalance($object)
    {
        $wallet = app(WalletService::class)->getWallet($object);

        return app(BookkeeperInterface::class)->amount($wallet);
    }

    /**
     * {@inheritdoc}
     */
    public function incBalance($object, $amount): string
    {
        $wallet = app(WalletService::class)->getWallet($object);

        return app(BookkeeperInterface::class)->increase($wallet, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBalance($object, $amount): bool
    {
        $wallet = app(WalletService::class)->getWallet($object);

        return app(BookkeeperInterface::class)->sync($wallet, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function fresh(): bool
    {
        return app(StorageInterface::class)->flush();
    }
}
