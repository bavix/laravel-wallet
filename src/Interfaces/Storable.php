<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Storable
{
    /**
     * Get balance from storage.
     *
     * @param Wallet $object
     *
     * @return float|int
     */
    public function getBalance($object);

    /**
     * We increase the balance by the amount.
     *
     * @param Wallet     $object
     * @param int|string $amount
     *
     * @return string
     */
    public function incBalance($object, $amount);

    /**
     * We set the exact amount.
     *
     * @param Wallet     $object
     * @param int|string $amount
     */
    public function setBalance($object, $amount): bool;

    /**
     * We clean the storage, a need for consumers.
     */
    public function fresh(): bool;
}
