<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Countable;

class BasketDto implements Countable
{
    /** @var ProductDto[] */
    private array $items;

    private array $meta;

    /** @param ProductDto[] $items */
    public function __construct(array $items, array $meta)
    {
        $this->items = $items;
        $this->meta = $meta;
    }

    public function metadata(): array
    {
        return $this->meta;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /** @return ProductDto[] */
    public function items(): array
    {
        return $this->items;
    }
}
