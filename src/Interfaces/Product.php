<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface Product extends Wallet
{
    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool;

    /**
     * @return float|int
     */
    public function getAmountProduct(Customer $customer);

    /**
     * @return array
     */
    public function getMetaProduct(): ?array;

    /**
     * @deprecated
     * @see Model::getKey()
     */
    public function getUniqueId(): string;
}
