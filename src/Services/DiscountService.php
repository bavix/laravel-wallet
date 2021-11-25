<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Discount;
use Bavix\Wallet\Interfaces\Wallet;

final class DiscountService implements DiscountServiceInterface
{
    public function getDiscount(Wallet $customer, Wallet $product): int
    {
        if ($customer instanceof Customer && $product instanceof Discount) {
            return (int) $product->getPersonalDiscount($customer);
        }

        return 0;
    }
}
