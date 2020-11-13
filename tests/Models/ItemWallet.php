<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Traits\HasWallets;

class ItemWallet extends Item
{
    use HasWallets;

    /**
     * @return string
     */
    public function getTable(): string
    {
        return 'items';
    }
}
