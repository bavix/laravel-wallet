<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\Taxable;

class ItemTax extends Item implements Taxable
{
    public function getTable(): string
    {
        return 'items';
    }

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
    public function getFeePercent()
    {
        return 7.5;
    }
}
