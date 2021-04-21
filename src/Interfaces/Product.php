<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Product extends Wallet
{
    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool;

    /** @return float|int|string */
    public function getAmountProduct(Customer $customer);

    public function getMetaProduct(): ?array;

    public function getUniqueId(): string;
}
