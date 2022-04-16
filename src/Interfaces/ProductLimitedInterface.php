<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface ProductLimitedInterface extends ProductInterface
{
    /**
     * The method is only needed for simple projects. For more complex projects, deprecate this method and redefine the
     * "BasketServiceInterface" interface. Typically, in projects, this method always returns false, and the presence
     * interface goes to the microservice and receives data on products.
     */
    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool;
}
