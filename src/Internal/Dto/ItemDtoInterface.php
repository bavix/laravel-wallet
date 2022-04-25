<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\ProductInterface;
use Countable;

interface ItemDtoInterface extends Countable
{
    /**
     * @return ProductInterface[]
     */
    public function getItems(): array;

    public function count(): int;

    public function getPricePerItem(): int|string|null;

    public function getProduct(): ProductInterface;
}
