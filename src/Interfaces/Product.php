<?php

namespace Bavix\Wallet\Interfaces;

interface Product extends Wallet
{

    /**
     * @param Customer $customer
     * @param bool $force
     *
     * @return bool
     */
    public function canBuy(Customer $customer, bool $force = false): bool;

    /**
     * @return int
     */
    public function getAmountProduct(): int;

    /**
     * @return array
     */
    public function getMetaProduct(): ?array;

}
