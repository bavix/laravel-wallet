<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\ProductInterface;

/** @psalm-immutable */
final class ItemDto implements ItemDtoInterface
{
    public function __construct(
        private readonly ProductInterface $product,
        private readonly int $quantity,
        private readonly int|string|null $pricePerItem,
    ) {
    }

    /**
     * @return ProductInterface[]
     */
    public function getItems(): array
    {
        return array_fill(0, $this->quantity, $this->product);
    }

    public function getPricePerItem(): int|string|null
    {
        return $this->pricePerItem;
    }

    public function getProduct(): ProductInterface
    {
        return $this->product;
    }

    public function count(): int
    {
        return $this->quantity;
    }
}
