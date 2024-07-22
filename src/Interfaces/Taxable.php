<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Taxable
{
    /**
     * Returns the percentage fee of the amount.
     *
     * This method should return the percentage fee of the amount.
     * For example, if the product costs $100 and the fee is 15%,
     * then the fee will be $15 ($115 - $100).
     *
     * The return value must be a float or integer.
     *
     * @return float|int The percentage fee of the amount.
     *
     * @example
     *
     * If the product costs $100 and the fee is 15%, then the fee will be $15 ($115 - $100).
     * If the product costs $100 and the fee is 7.5%, then the fee will be $7.5 ($107.5 - $100).
     */
    public function getFeePercent(): float|int;
}
