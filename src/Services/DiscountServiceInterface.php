<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;

/**
 * @api
 */
interface DiscountServiceInterface
{
    /**
     * Calculates the discount amount for the given customer and product.
     *
     * The discount amount is calculated by subtracting the product's price from the customer's available balance.
     * This method is used to determine the cost of the product after applying the discount.
     *
     * @param Wallet $customer The wallet object representing the customer. The discount is calculated based on the
     *                        customer's available balance.
     * @param Wallet $product The wallet object representing the product. The price of the product is used to calculate
     *                        the discount amount.
     * @return int The discount amount. The value is negative and represents the amount that the customer will pay less
     *             for the product. The returned value is the sum of the product's price and the discount amount.
     *
     * @see \Bavix\Wallet\Interfaces\Wallet::getBalance()
     * @see \Bavix\Wallet\Interfaces\Wallet::getAmount()
     */
    public function getDiscount(Wallet $customer, Wallet $product): int;
}
