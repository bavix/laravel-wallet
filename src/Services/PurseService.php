<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Illuminate\Database\Eloquent\Model;

class PurseService
{
    public function getPurseId(Wallet $wallet): string
    {
        // @var Model $wallet
        return (string) $wallet->getKey();
    }
}
