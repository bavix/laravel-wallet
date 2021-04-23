<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Model;

interface CastInterface
{
    public function getHolderModel(Wallet $wallet): Model;

    public function findHolderModel(Wallet $wallet): ?Model;

    public function getWalletModel(Wallet $wallet): WalletModel;

    public function findWalletModel(Wallet $wallet): ?WalletModel;
}
