<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Discount
{
    /**
     * @return float|int
     */
    public function getPersonalDiscount(Customer $customer);
}
