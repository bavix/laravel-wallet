<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\ProductInterface;

/** @psalm-immutable */
final class ItemDto implements ItemDtoInterface
{
    public function __construct(
        private ProductInterface $product,
        private int $quantity,
        private int|string|null $price,
    ) {
    }

    /**
     * @return ProductInterface[]
     */
    public function items(): array
    {
        return array_fill(0, $this->quantity, $this->product);
    }

    public function getPrice(): int|string|null
    {
        return $this->price;
    }

    public function product(): ProductInterface
    {
        return $this->product;
    }

    public function count(): int
    {
        return $this->quantity;
    }
}
