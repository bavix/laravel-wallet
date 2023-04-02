<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface ProductInterface extends Wallet
{
    public function getAmountProduct(Customer $customer, ?string $currency = null): int|string;

    /**
     * @return array<mixed>|null
     */
    public function getMetaProduct(): ?array;
}
