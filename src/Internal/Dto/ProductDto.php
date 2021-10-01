<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Product;
use Countable;

class ProductDto implements Countable
{
    private Product $product;

    private int $quantity;

    public function __construct(Product $product, int $quantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
    }

    public function item(): Product
    {
        return $this->product;
    }

    public function count(): int
    {
        return $this->quantity;
    }
}
