<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Discount
{
    public function getPersonalDiscount(Customer $customer): float|int;
}
