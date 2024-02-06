<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Discount;
use Bavix\Wallet\Interfaces\Wallet;

/**
 * @internal
 */
final class DiscountService implements DiscountServiceInterface
{
    public function getDiscount(Wallet $customer, Wallet $product): int
    {
        if (! $customer instanceof Customer) {
            return 0;
        }
        if (! $product instanceof Discount) {
            return 0;
        }
        return (int) $product->getPersonalDiscount($customer);
    }
}
