<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface ProductLimitedInterface extends ProductInterface
{
    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool;
}
