<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\AvailabilityDtoInterface;

/**
 * @api
 */
interface BasketServiceInterface
{
    /**
     * Checks the availability of products in the basket.
     *
     * This method is used to quickly check the stock of products in the basket.
     * It allows batch checks, which is necessary for quick payments.
     *
     * @param AvailabilityDtoInterface $availabilityDto The DTO containing the basket and customer information.
     *                                                The DTO contains the basket DTO and the customer object.
     * @return bool True if all products are available, false otherwise.
     *              Returns true if all products in the basket are available, false otherwise.
     *              The method checks the availability of each product in the basket.
     *              If any product is not available, the method returns false.
     *              If all products are available, the method returns true.
     */
    public function availability(AvailabilityDtoInterface $availabilityDto): bool;
}
