<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Product;
use Countable;
use Generator;

interface BasketDtoInterface extends Countable
{
    public function total(): int;

    public function meta(): array;

    /** @return non-empty-array<int|string, ItemDtoInterface> */
    public function items(): array;

    /** @return Generator<array-key, Product, mixed, void> */
    public function cursor(): Generator;
}
