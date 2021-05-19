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
    /** @param Model|Wallet $model */
    public function getHolderModel($model): Model
    {
        return $this->findOrFail([$this, 'findHolderModel'], $model);
    }

    /** @param Model|Wallet $model */
    public function findHolderModel($model): ?Model
    {
        if ($model instanceof WalletModel) {
            return $model->holder;
        }

        if ($model instanceof Model) {
            return $model;
        }

        return null;
    }

    /** @param Model|Wallet $model */
    public function getWalletModel($model): WalletModel
    {
        return $this->findOrFail([$this, 'findWalletModel'], $model);
    }

    /** @param Model|Wallet $model */
    public function findWalletModel($model): ?WalletModel
    {
        if ($model instanceof WalletModel) {
            return $model;
        }

        if (method_exists($model, 'wallet')) {
            /** @var HasWallet $model */
            return $model->wallet;
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
