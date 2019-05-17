<?php

namespace Bavix\Wallet\Interfaces;

interface Group
{

    /**
     * @param Product $product
     * @return Group
     */
    public function addItem(Product $product): self;

    /**
     * @return Product[]
     */
    public function getItems(): array;

}
