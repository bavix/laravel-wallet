<?php

namespace Bavix\Wallet\Interfaces;

interface Discount extends Product
{
    /**
     * @return float|int
     */
    public function getPersonalDiscount(Customer $customer);
}
