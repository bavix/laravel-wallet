<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\ProductLimitedInterface;
use Bavix\Wallet\Internal\Dto\AvailabilityDtoInterface;

/**
 * @internal
 */
final class BasketService implements BasketServiceInterface
{
    public function availability(AvailabilityDtoInterface $availabilityDto): bool
    {
        $basketDto = $availabilityDto->getBasketDto();
        $customer = $availabilityDto->getCustomer();
        foreach ($basketDto->items() as $itemDto) {
            $product = $itemDto->getProduct();
            if ($product instanceof ProductLimitedInterface && ! $product->canBuy(
                $customer,
                $itemDto->count(),
                $availabilityDto->isForce()
            )) {
                return false;
            }
        }

        return true;
    }
}
