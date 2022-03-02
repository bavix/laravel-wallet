<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface Taxable
{
    /**
     * Specify the percentage of the amount.
     * For example, the product costs $100, the equivalent of 15%.
     * That's $115.
     *
     * Minimum 0; Maximum 100
     * Example: return 7.5; // 7.5%
     *
     * @return float|int
     */
    public function getFeePercent();
}
