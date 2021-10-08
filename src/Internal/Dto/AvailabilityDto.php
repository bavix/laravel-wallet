<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Customer;

class AvailabilityDto
{
    private BasketDto $basketDto;

    private Customer $customer;

    private bool $force;

    public function __construct(
        Customer $customer,
        BasketDto $basketDto,
        bool $force = false
    ) {
        $this->customer = $customer;
        $this->basketDto = $basketDto;
        $this->force = $force;
    }

    public function getBasketDto(): BasketDto
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
