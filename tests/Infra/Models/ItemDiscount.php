<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Discount;
use Bavix\Wallet\Services\CastService;

class ItemDiscount extends Item implements Discount
{
    public function getTable(): string
    {
        return 'items';
    }

    public function getPersonalDiscount(Customer $customer): int
    {
        return (int) app(CastService::class)
            ->getWallet($customer)
            ->holder_id;
    }
}
