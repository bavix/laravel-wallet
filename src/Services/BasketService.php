<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\BasketInterface;
use Bavix\Wallet\Internal\Dto\AvailabilityDto;

class BasketService implements BasketInterface
{
    public function availability(AvailabilityDto $availabilityDto): bool
    {
        $basketDto = $availabilityDto->getBasketDto();
        $customer = $availabilityDto->getCustomer();
        foreach ($basketDto->items() as $productDto) {
            if (!$productDto->product()->canBuy($customer, $productDto->count(), $availabilityDto->isForce())) {
                return false;
            }
        }

        return true;
    }
}
