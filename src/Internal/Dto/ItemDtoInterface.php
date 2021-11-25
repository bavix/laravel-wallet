<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Product;
use Countable;

interface ItemDtoInterface extends Countable
{
    /** @return Product[] */
    public function items(): array;

    public function count(): int;

    public function product(): Product;
}
