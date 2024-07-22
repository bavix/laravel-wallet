<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Discount
{
    /**
     * Receive a personal discount for the client.
     *
     * This method should return a numeric value of how much cheaper the selected product will be for a specific customer.
     *
     * The method returns a value that will be subtracted from the cost of the product.
     * For example, a product costs 100 and the method returns 75, then the product for the client will cost 25.
     */
    public function getPersonalDiscount(Customer $customer): float|int;
}
