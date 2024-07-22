<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Customer;

interface AvailabilityDtoInterface
{
    /**
     * Returns the basket DTO object.
     */
    public function getBasketDto(): BasketDtoInterface;

    /**
     * Returns the customer object.
     */
    public function getCustomer(): Customer;

    /**
     * Returns whether the creation is forced.
     */
    public function isForce(): bool;
}
