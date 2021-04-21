<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Discount;
use Bavix\Wallet\Services\WalletService;

class ItemDiscount extends Item implements Discount
{
    public function getTable(): string
    {
        return 'items';
    }

    public function getPersonalDiscount(Customer $customer)
    {
        return app(WalletService::class)
            ->getWallet($customer)
            ->holder_id;
    }
}
