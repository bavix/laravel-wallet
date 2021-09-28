<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Traits\HasWallets;

class ItemWallet extends Item
{
    use HasWallets;

    public function getTable(): string
    {
        return 'items';
    }
}
