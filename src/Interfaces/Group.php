<?php

namespace Bavix\Wallet\Interfaces;

interface Group
{

    /**
     * @param Product $product
     * @return static
     */
    public function addItem(Product $product): self;

    /**
     * @return Product[]
     */
    public function getItems(): array;

}
