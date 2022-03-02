<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Internal\Dto\BasketDtoInterface;

interface CartInterface
{
    public function getBasketDto(): BasketDtoInterface;
}
