<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Internal\Exceptions\CartEmptyException;

/**
 * A kind of cart hydrate, needed for a smooth transition from a convenient dto to a less convenient internal dto.
 */
interface CartInterface
{
    /**
     * @throws CartEmptyException
     */
    public function getBasketDto(): BasketDtoInterface;
}
