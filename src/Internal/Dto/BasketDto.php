<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Product;
use Generator;

final class BasketDto implements BasketDtoInterface
{
    /** @var non-empty-array<int|string, ItemDtoInterface> */
    private array $items;

    private array $meta;

    /** @param non-empty-array<int|string, ItemDtoInterface> $items */
    public function __construct(array $items, array $meta)
    {
        $this->items = $items;
        $this->meta = $meta;
    }

    public function meta(): array
    {
        return $this->meta;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function total(): int
    {
        return iterator_count($this->cursor());
    }

    /** @return Generator<array-key, Product, mixed, void> */
    public function cursor(): Generator
    {
        foreach ($this->items as $item) {
            yield from $item->items();
        }
    }

    /** @return non-empty-array<int|string, ItemDtoInterface> */
    public function items(): array
    {
        return $this->items;
    }
}
