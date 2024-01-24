<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\ProductInterface;
use Generator;

final readonly class BasketDto implements BasketDtoInterface
{
    /**
     * @param non-empty-array<int|string, ItemDtoInterface> $items
     * @param array<mixed> $meta
     * @param array<mixed>|null $extra
     */
    public function __construct(
        private array $items,
        private array $meta,
        private ?array $extra,
    ) {
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

    /**
     * @return Generator<array-key, ProductInterface, mixed, void>
     */
    public function cursor(): Generator
    {
        foreach ($this->items as $item) {
            foreach ($item->getItems() as $product) {
                yield $product;
            }
        }
    }

    /**
     * @return non-empty-array<int|string, ItemDtoInterface>
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @return array<mixed>|null
     */
    public function extra(): ?array
    {
        return $this->extra;
    }
}
