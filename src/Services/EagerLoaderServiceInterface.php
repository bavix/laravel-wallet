<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;

/**
 * This class is responsible for eager loading wallets by basket.
 *
 * Eager loading is a database query optimization technique that helps to reduce the number of queries executed in
 * a program by preloading all of the related data at once.
 *
 * This interface provides a method for loading wallets by basket.
 *
 * @api
 */
interface EagerLoaderServiceInterface
{
    /**
     * Load wallets by basket.
     *
     * This method is responsible for loading wallets by basket.
     * The Customer object represents the customer who created the basket.
     * The BasketDtoInterface object represents the basket.
     *
     * @param Customer $customer The customer who created the basket.
     * @param BasketDtoInterface $basketDto The basket.
     */
    public function loadWalletsByBasket(Customer $customer, BasketDtoInterface $basketDto): void;
}
