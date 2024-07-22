<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface ProductLimitedInterface extends ProductInterface
{
    /**
     * Check if the customer can buy the product.
     *
     * @param Customer $customer The customer entity
     * @param int $quantity The quantity of the product to buy. Default is 1.
     * @param bool $force Flag to force the purchase. Default is false.
     * @return bool Returns true if the customer can buy the product, false otherwise.
     *
     * The method checks if the customer can buy the product based on the quantity and the force flag.
     * If the force flag is set to true, the method returns true regardless of the quantity.
     * If the force flag is set to false, the method returns true if the quantity of the product is
     * greater than or equal to the quantity to buy.
     *
     * The method does not check if the customer has already bought the product. It is the responsibility
     * of the caller to check if the customer has already bought the product.
     */
    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool;
}
