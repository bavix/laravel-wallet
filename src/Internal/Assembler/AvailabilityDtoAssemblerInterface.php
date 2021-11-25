<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\AvailabilityDtoInterface;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;

interface AvailabilityDtoAssemblerInterface
{
    public function create(Customer $customer, BasketDtoInterface $basketDto, bool $force): AvailabilityDtoInterface;
}
