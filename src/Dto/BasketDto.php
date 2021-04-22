<?php

declare(strict_types=1);

namespace Bavix\Wallet\Dto;

use Bavix\Wallet\Interfaces\Product;

class BasketDto
{
    private string $idempotencyKey;

    /** @var Product[] */
    private array $items;

    private ?array $meta;

    public function __construct(string $idempotencyKey, array $items, ?array $meta)
    {
        $this->idempotencyKey = $idempotencyKey;
        $this->items = $items;
        $this->meta = $meta;
    }

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
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
