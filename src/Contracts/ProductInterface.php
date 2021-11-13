<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

interface ProductInterface extends WalletInterface
{
    public function canBuy(CustomerInterface $customer, int $quantity = 1, bool $force = false): bool;

    /**
     * @return float|int|string
     */
    public function getAmountProduct(CustomerInterface $customer);

    /**
     * @return array
     */
    public function getMetaProduct(): ?array;
}
