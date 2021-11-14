<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Customer;

interface AvailabilityDtoInterface
{
    public function getBasketDto(): BasketDtoInterface;

    public function getCustomer(): Customer;

    public function isForce(): bool;
}
