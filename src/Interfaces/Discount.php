<?php

namespace Bavix\Wallet\Interfaces;

interface Discount extends Product
{
    /**
     * @param Customer $customer
     * @return int
     */
    public function getDiscountProduct(Customer $customer): int;
}
