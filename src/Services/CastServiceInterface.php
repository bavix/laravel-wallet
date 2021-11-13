<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as WalletModel;
use Illuminate\Database\Eloquent\Model;

interface CastServiceInterface
{
    public function getWallet(Wallet $object, bool $save = true): WalletModel;

    /** @param Model|Wallet $object */
    public function getHolder($object): Model;

    public function getModel(object $object): Model;
}
