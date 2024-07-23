<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Internal\Exceptions\CartEmptyException;

/**
 * The `CartInterface` is a kind of cart hydrate, needed for a smooth transition
 * from a convenient DTO to a less convenient internal DTO.
 */
interface CartInterface
{
    /**
     * Returns the basket DTO containing the products and their metadata.
     *
     * When the cart is empty, this method will throw a `CartEmptyException`.
     *
     * @return BasketDtoInterface The basket DTO.
     *
     * @throws CartEmptyException If the cart is empty.
     */
    public function getBasketDto(): BasketDtoInterface;
}
