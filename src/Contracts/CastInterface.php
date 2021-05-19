<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Model;

interface CastInterface
{
    /** @param Model|Wallet $model */
    public function getHolderModel($model): Model;

    /** @param Model|Wallet $model */
    public function findHolderModel($model): ?Model;

    /** @param Model|Wallet $model */
    public function getWalletModel($model): WalletModel;

    /** @param Model|Wallet $model */
    public function findWalletModel($model): ?WalletModel;
}
