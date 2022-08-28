<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\AvailabilityDtoInterface;

interface BasketServiceInterface
{
    /**
     * A quick way to check stock. Able to check in batches, necessary for quick payments.
     */
    public function availability(AvailabilityDtoInterface $availabilityDto): bool;
}
