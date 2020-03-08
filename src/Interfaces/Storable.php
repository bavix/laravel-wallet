<?php

namespace Bavix\Wallet\Interfaces;

interface Storable
{
    /**
     * @param Wallet $object
     * @return int|float
     */
    public function getBalance($object);

    /**
     * @param Wallet $object
     * @param int $amount
     * @return int|float
     */
    public function incBalance($object, $amount);

    /**
     * @param Wallet $object
     * @param int $amount
     * @return bool
     */
    public function setBalance($object, $amount): bool;
}
