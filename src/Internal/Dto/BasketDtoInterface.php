<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\ProductInterface;
use Countable;
use Generator;

interface BasketDtoInterface extends Countable
{
    /**
     * Calculate the total amount of the basket.
     */
    public function total(): int;

    /**
     * Retrieve the metadata of the basket.
     *
     * @return array<mixed>
     */
    public function meta(): array;

    /**
     * Retrieve the extra data of the basket.
     *
     * @return array<mixed>|null
     */
    public function extra(): ?array;

    /**
     * Retrieve the items of the basket.
     *
     * @return non-empty-array<int|string, ItemDtoInterface>
     */
    public function items(): array;

    /**
     * Retrieve the generator for the items of the basket.
     *
     * @return Generator<ProductInterface>
     */
    public function cursor(): Generator;
}
