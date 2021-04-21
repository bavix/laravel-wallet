<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Storable
{
    /** @retrun int|float|string */
    public function getBalance(Wallet $object);

    /** @param float|int|string $amount */
    public function incBalance(Wallet $object, $amount);

    /** @param float|int|string $amount */
    public function setBalance(Wallet $object, $amount): bool;

    public function fresh(): bool;
}
