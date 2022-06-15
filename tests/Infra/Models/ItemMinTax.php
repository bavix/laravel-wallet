<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\Interfaces\MinimalTaxable;

class ItemMinTax extends Item implements MinimalTaxable
{
    public function getTable(): string
    {
        return 'items';
    }

    public function getFeePercent(): float
    {
        return 3;
    }

    public function getMinimalFee(): int
    {
        return 90;
    }
}
