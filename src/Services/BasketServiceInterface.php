<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\AvailabilityDtoInterface;

interface BasketServiceInterface
{
    public function availability(AvailabilityDtoInterface $availabilityDto): bool;
}
