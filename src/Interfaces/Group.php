<?php

namespace Bavix\Wallet\Interfaces;

interface Group
{

    /**
     * @param Product $product
     */
    public function addItem(Product $product): void;

    /**
     * @return Product[]
     */
    public function getItems(): array;

}
