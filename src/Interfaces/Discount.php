<?php

namespace Bavix\Wallet\Interfaces;

interface Discount extends Product
{
    /**
     * @param Customer $customer
     * @return int
     */
    public function getPersonalDiscount(Customer $customer): int;
}
