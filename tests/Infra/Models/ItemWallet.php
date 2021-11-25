<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\Traits\HasWallets;

class ItemWallet extends Item
{
    use HasWallets;

    public function getTable(): string
    {
        return 'items';
    }
}
