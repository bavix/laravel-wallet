<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface ProductInterface extends Wallet
{
    public function getAmountProduct(Customer $customer): int|string;

    public function getMetaProduct(): ?array;
}
