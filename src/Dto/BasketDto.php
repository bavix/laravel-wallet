<?php

declare(strict_types=1);

namespace Bavix\Wallet\Dto;

use Bavix\Wallet\Interfaces\Product;

class BasketDto
{
    /** @var Product[] */
    private array $items;

    private ?array $meta;

    public function __construct(array $items, ?array $meta)
    {
        $this->items = $items;
        $this->meta = $meta;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    /** @return Product[] */
    public function getItems(): array
    {
        return $this->items;
    }
}
