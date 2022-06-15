<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Models;

use Bavix\Wallet\Interfaces\MaximalTaxable;

class ItemMaxTax extends Item implements MaximalTaxable
{
    public function getTable(): string
    {
        return 'items';
    }

    public function getFeePercent(): float
    {
        return 3;
    }

    public function getMaximalFee(): int
    {
        return 300;
    }
}
