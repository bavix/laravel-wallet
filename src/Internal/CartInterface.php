<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal;

use Bavix\Wallet\Internal\Dto\BasketDto;
use Bavix\Wallet\Internal\Exceptions\CartEmptyException;

interface CartInterface
{
    /**
     * @throws CartEmptyException
     */
    public function getBasketDto(): BasketDto;
}
