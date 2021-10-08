<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Countable;

class BasketDto implements Countable
{
    /** @var ItemDto[] */
    private array $items;

    private array $meta;

    /** @param ItemDto[] $items */
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
        return count(array_merge(...array_map(static fn (ItemDto $dto) => $dto->items(), $this->items)));
    }

    public function cursor(): iterable
    {
        foreach ($this->items as $item) {
            yield from $item->items();
        }
    }

    /** @return ItemDto[] */
    public function items(): iterable
    {
        return $this->items;
    }
}
