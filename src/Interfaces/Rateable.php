<?php

namespace Bavix\Wallet\Interfaces;

interface Rateable
{
    /**
     * @param int|string $amount
     * @return Rateable
     */
    public function withAmount($amount): self;

    /**
     * @param Wallet $wallet
     * @return self
     */
    public function withCurrency(Wallet $wallet): self;

    /**
     * @param Wallet $wallet
     * @return int|float
     */
    public function convertTo(Wallet $wallet);
}
