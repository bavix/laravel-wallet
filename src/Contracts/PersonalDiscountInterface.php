<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

interface PersonalDiscountInterface
{
    /**
     * @return float|int
     */
    public function getPersonalDiscount(CustomerInterface $customer);
}
