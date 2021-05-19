<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

/**
 * @deprecated
 * @use RateInterface
 */
interface Rateable
{
    /** @param float|int|string $amount */
    public function withAmount($amount): self;

    public function withCurrency(Wallet $wallet): self;

    public function convertTo(Wallet $wallet): string;
}
