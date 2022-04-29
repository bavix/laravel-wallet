<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\BasketDtoInterface;

interface EagerLoaderServiceInterface
{
    public function loadWalletsByBasket(BasketDtoInterface $basketDto): void;
}
