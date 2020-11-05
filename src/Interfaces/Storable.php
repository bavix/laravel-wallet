<?php

namespace Bavix\Wallet\Interfaces;

interface Storable
{
    /**
     * Get balance from storage.
     *
     * @param Wallet $object
     * @return int|float
     */
    public function getBalance($object);

    /**
     * We increase the balance by the amount.
     *
     * @param Wallet $object
     * @param int|string $amount
     * @return string
     */
    public function incBalance($object, $amount);

    /**
     * We set the exact amount.
     *
     * @param Wallet $object
     * @param int|string $amount
     * @return bool
     */
    public function setBalance($object, $amount): bool;

    /**
     * We clean the storage, a need for consumers.
     *
     * @return bool
     */
    public function fresh(): bool;
}
