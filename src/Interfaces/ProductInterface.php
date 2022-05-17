<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\External\Contracts\CostDtoInterface;

interface ProductInterface extends Wallet
{
    public function getAmountProduct(Customer $customer): CostDtoInterface|int|string;

    /**
     * @return array<mixed>|null
     */
    public function getMetaProduct(): ?array;
}
