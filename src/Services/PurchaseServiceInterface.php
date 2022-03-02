<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Models\Transfer;

interface PurchaseServiceInterface
{
    /** @return Transfer[] */
    public function already(Customer $customer, BasketDtoInterface $basketDto, bool $gifts = false): array;
}
