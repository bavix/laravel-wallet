<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;

/**
 * @api
 */
interface DiscountServiceInterface
{
    public function getDiscount(Wallet $customer, Wallet $product): int;
}
