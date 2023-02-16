<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Customer;

/** @immutable */
final class AvailabilityDto implements AvailabilityDtoInterface
{
    public function __construct(
        private readonly Customer $customer,
        private readonly BasketDtoInterface $basketDto,
        private readonly bool $force
    ) {
    }

    public function getBasketDto(): BasketDtoInterface
    {
        return $this->basketDto;
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function isForce(): bool
    {
        return $this->force;
    }
}
