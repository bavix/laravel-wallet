<?php

namespace Bavix\Wallet\Interfaces;

interface Product extends Wallet
{

    /**
     * @param Customer $customer
     *
     * @return bool
     */
    public function canBuy(Customer $customer): bool;

    /**
     * @return int
     */
    public function getAmountProduct(): int;

    /**
     * @return array
     */
    public function getMetaProduct(): ?array;
    
}
