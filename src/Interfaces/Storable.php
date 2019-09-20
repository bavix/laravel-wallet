<?php

namespace Bavix\Wallet\Interfaces;

interface Storable
{
    /**
     * @param Wallet $object
     * @return int
     */
    public function getBalance($object): int;

    /**
     * @param Wallet $object
     * @param int $amount
     * @return int
     */
    public function incBalance($object, int $amount): int;

    /**
     * @param Wallet $object
     * @param int $amount
     * @return bool
     */
    public function setBalance($object, int $amount): bool;
}
