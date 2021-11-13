<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Contracts\CustomerInterface;

/** @psalm-immutable */
final class AvailabilityDto
{
    private BasketDto $basketDto;

    private CustomerInterface $customer;

    private bool $force;

    public function __construct(
        CustomerInterface $customer,
        BasketDto $basketDto,
        bool $force
    ) {
        $this->customer = $customer;
        $this->basketDto = $basketDto;
        $this->force = $force;
    }

    public function getBasketDto(): BasketDto
    {
        return $this->basketDto;
    }

    public function getCustomer(): CustomerInterface
    {
        return $this->customer;
    }

    public function isForce(): bool
    {
        return $this->force;
    }
}
