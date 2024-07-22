<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\AvailabilityDtoInterface;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;

interface AvailabilityDtoAssemblerInterface
{
    /**
     * Create a new AvailabilityDto instance.
     *
     * @param Customer $customer The customer object
     * @param BasketDtoInterface $basketDto The basket DTO object
     * @param bool $force Whether the creation is forced
     * @return AvailabilityDtoInterface The created Availability DTO instance
     */
    public function create(Customer $customer, BasketDtoInterface $basketDto, bool $force): AvailabilityDtoInterface;
}
