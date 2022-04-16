<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

/**
 * @internal
 */
interface ProductInterface extends Wallet
{
    /**
     * @return float|int|string
     */
    public function getAmountProduct(Customer $customer);

    public function getMetaProduct(): ?array;
}
