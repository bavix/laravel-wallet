<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

/**
 * @deprecated The interface was not thought out
 * @see ExchangeInterface
 */
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
