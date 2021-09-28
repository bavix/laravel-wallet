<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\MinimalTaxable;

class ItemMinTax extends Item implements MinimalTaxable
{
    /**
     * {@inheritdoc}
     */
    public function getTable(): string
    {
        return 'items';
    }

    /**
     * {@inheritdoc}
     */
    public function getFeePercent(): float
    {
        return 3;
    }

    public function getMinimalFee(): int
    {
        return 90;
    }
}
