<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\ProductInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Countable;

interface ItemDtoInterface extends Countable
{
    /**
     * Returns an array of items in the DTO.
     *
     * @return ProductInterface[]
     */
    public function getItems(): array;

    /**
     * Returns the price per item in the DTO.
     */
    public function getPricePerItem(): int|string|null;

    /**
     * Returns the product in the DTO.
     */
    public function getProduct(): ProductInterface;

    /**
     * Returns the receiving wallet in the DTO.
     */
    public function getReceiving(): ?Wallet;
}
