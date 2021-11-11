<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Discount;
use Bavix\Wallet\Internal\Service\CastService;

class ItemDiscount extends Item implements Discount
{
    public function getTable(): string
    {
        return 'items';
    }

    public function getPersonalDiscount(Customer $customer): int
    {
        return app(CastService::class)
            ->getWallet($customer)
            ->holder_id;
    }
}
