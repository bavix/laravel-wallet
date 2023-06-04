<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\ProductInterface;
use Bavix\Wallet\Interfaces\Wallet;

/** @immutable */
final class ItemDto implements ItemDtoInterface
{
    public function __construct(
        private readonly ProductInterface $product,
        private readonly int $quantity,
        private readonly int|string|null $pricePerItem,
        private readonly ?Wallet $receiving,
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

    public function getReceiving(): ?Wallet
    {
        return $this->receiving;
    }
}
