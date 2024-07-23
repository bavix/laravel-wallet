<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Models\Transfer;

/**
 * @api
 */
interface PurchaseServiceInterface
{
    /**
     * Retrieve an array of already purchased transfers for a given customer and basket.
     *
     * This method retrieves an array of already purchased transfers for a given customer and basket.
     * The customer and basket are defined by the Customer and BasketDtoInterface objects respectively.
     *
     * @param Customer $customer The customer to retrieve transfers for.
     * @param BasketDtoInterface $basketDto The basket to retrieve transfers for.
     * @param bool $gifts [optional] Whether to only retrieve gift transfers or not. Default is false.
     * @return Transfer[] An array of already purchased transfers.
     */
    public function already(Customer $customer, BasketDtoInterface $basketDto, bool $gifts = false): array;
}
