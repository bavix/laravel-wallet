<?php

namespace Bavix\Wallet\Interfaces;

interface Product extends Wallet
{

    /**
     * @return bool
     */
    public function canBuy(): bool;

    /**
     * @return int
     */
    public function getAmountProduct(): int;

    /**
     * @return array
     */
    public function getMetaProduct(): ?array;
    
}
