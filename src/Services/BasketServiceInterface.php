<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\AvailabilityDto;

interface BasketServiceInterface
{
    public function availability(AvailabilityDto $availabilityDto): bool;
}
