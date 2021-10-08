<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\BasketDto;
use Bavix\Wallet\Models\Transfer;

interface PurchaseInterface
{
    /** @return Transfer[] */
    public function already(Customer $customer, BasketDto $basketDto, bool $gifts = false): array;
}
