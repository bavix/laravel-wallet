<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;

/**
 * Ad hoc solution... Needed for internal purposes only. Helps to optimize greedy queries inside laravel.
 *
 * @api
 */
interface EagerLoaderServiceInterface
{
    public function loadWalletsByBasket(Customer $customer, BasketDtoInterface $basketDto): void;
}
