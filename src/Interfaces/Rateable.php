<?php

namespace Bavix\Wallet\Interfaces;

interface Rateable
{

    /**
     * @param int $amount
     * @return Rateable
     */
    public function withAmount(int $amount): self;

    /**
     * @param Wallet $wallet
     * @return self
     */
    public function withCurrency(Wallet $wallet): self;

    /**
     * @param Wallet $wallet
     * @return float
     */
    public function convertTo(Wallet $wallet): float;

}
