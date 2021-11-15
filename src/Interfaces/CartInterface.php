<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Internal\Exceptions\CartEmptyException;

interface CartInterface
{
    /**
     * @throws CartEmptyException
     */
    public function getBasketDto(): BasketDtoInterface;
}
