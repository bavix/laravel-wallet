<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Customer;

/** @psalm-immutable */
final class AvailabilityDto implements AvailabilityDtoInterface
{
    private BasketDtoInterface $basketDto;

    private Customer $customer;

    private bool $force;

    public function __construct(
        Customer $customer,
        BasketDtoInterface $basketDto,
        bool $force
    ) {
        $this->customer = $customer;
        $this->basketDto = $basketDto;
        $this->force = $force;
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
