<?php

namespace Bavix\Wallet\Interfaces;

interface Rateable
{
    /**
     * @param int|string $amount
     *
     * @return Rateable
     */
    public function withAmount($amount): self;

    public function withCurrency(Wallet $wallet): self;

    /**
     * @return float|int
     */
    public function convertTo(Wallet $wallet);
}
