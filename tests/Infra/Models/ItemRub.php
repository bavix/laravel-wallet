<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\External\Contracts\CostDtoInterface;
use Bavix\Wallet\External\Dto\Cost;
use Bavix\Wallet\Interfaces\Customer;

class ItemRub extends Item
{
    public function getTable(): string
    {
        return 'items';
    }

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        return true;
    }

    public function getAmountProduct(Customer $customer): CostDtoInterface
    {
        return new Cost($this->price, 'RUB');
    }
}
