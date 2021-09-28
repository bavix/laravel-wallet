<?php

namespace Bavix\Wallet\Interfaces;

interface Product extends Wallet
{
    /**
     * @param bool $force
     */
    public function canBuy(Customer $customer, int $quantity = 1, bool $force = null): bool;

    /**
     * @return float|int
     */
    public function getAmountProduct(Customer $customer);

    /**
     * @return array
     */
    public function getMetaProduct(): ?array;

    public function getUniqueId(): string;
}
