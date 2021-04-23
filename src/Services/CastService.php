<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\CastInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Bavix\Wallet\Traits\HasWallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CastService implements CastInterface
{
    public function getHolderModel(Wallet $wallet): Model
    {
        return $this->findOrFail([$this, 'findHolderModel'], $wallet);
    }

    public function findHolderModel(Wallet $wallet): ?Model
    {
        if ($wallet instanceof WalletModel) {
            return $wallet->holder;
        }

        if ($wallet instanceof Model) {
            return $wallet;
        }

        return null;
    }

    public function getWalletModel(Wallet $wallet): WalletModel
    {
        return $this->findOrFail([$this, 'findWalletModel'], $wallet);
    }

    public function findWalletModel(Wallet $wallet): ?WalletModel
    {
        if ($wallet instanceof WalletModel) {
            return $wallet;
        }

        if (method_exists($wallet, 'wallet')) {
            /** @var HasWallet $wallet */
            return $wallet->wallet;
        }

        return null;
    }

    private function findOrFail(callable $callable, ...$args)
    {
        if (($model = $callable(...$args)) === null) {
            throw new ModelNotFoundException();
        }

        return $model;
    }
}
