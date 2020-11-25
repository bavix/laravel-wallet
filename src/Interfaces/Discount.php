<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Discount extends Product
{
    /**
     * @param Customer $customer
     * @return int|float
     */
    public function getPersonalDiscount(Customer $customer);
}
