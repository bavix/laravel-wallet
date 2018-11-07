<?php

namespace Bavix\Wallet\Interfaces;

interface Product extends Wallet
{

    /**
     * @return int
     */
    public function getAmountProduct(): int;

    /**
     * @return array
     */
    public function getMetaProduct(): ?array;
    
}
