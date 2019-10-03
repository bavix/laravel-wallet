<?php

namespace Bavix\Wallet\Interfaces;

interface Product extends Wallet
{

    /**
     * @param Customer $customer
     * @param int $quantity
     * @param bool $force
     *
     * @return bool
     */
    public function canBuy(Customer $customer, int $quantity = 1, bool $force = null): bool;

    /**
     * @param Customer $customer
     * @return int
     */
    public function getAmountProduct(Customer $customer): int;

    /**
     * @return array
     */
    public function getMetaProduct(): ?array;

    /**
     * @return string
     */
    public function getUniqueId(): string;
}
