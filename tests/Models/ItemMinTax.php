<?php

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Interfaces\MinimalTaxable;

class ItemMinTax extends Item implements MinimalTaxable
{
    public function getTable(): string
    {
        return 'items';
    }

    public function getFeePercent(): string
    {
        return (string) 3;
    }

    public function getMinimalFee(): string
    {
        return (string) 90;
    }
}
