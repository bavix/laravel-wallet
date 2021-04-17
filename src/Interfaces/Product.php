<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Product extends Wallet
{
    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool;

    public function getAmountProduct(Customer $customer): string;

    public function getMetaProduct(): ?array;

    public function getUniqueId(): string;
}
