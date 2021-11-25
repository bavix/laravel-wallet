<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Product;

/** @psalm-immutable */
final class ItemDto implements ItemDtoInterface
{
    private Product $product;
    private int $quantity;

    public function __construct(Product $product, int $quantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
    }

    /**
     * @return Product[]
     */
    public function items(): array
    {
        return array_fill(0, $this->quantity, $this->product);
    }

    public function product(): Product
    {
        return $this->product;
    }

    public function count(): int
    {
        return $this->quantity;
    }
}
