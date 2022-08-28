<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\BasketDtoInterface;

/**
 * Ad hoc solution... Needed for internal purposes only. Helps to optimize greedy queries inside laravel.
 */
interface EagerLoaderServiceInterface
{
    public function loadWalletsByBasket(BasketDtoInterface $basketDto): void;
}
