<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Discount;
use Bavix\Wallet\Services\WalletService;

class ItemDiscount extends Item implements Discount
{

    /**
     * @return string
     */
    public function getTable(): string
    {
        return 'items';
    }

    /**
     * @param Customer $customer
     * @return int
     */
    public function getPersonalDiscount(Customer $customer): int
    {
        $wallet = app(WalletService::class)
            ->getWallet($customer);

        return $wallet->holder->id;
    }

}
