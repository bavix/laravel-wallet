<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Models;

use Bavix\Wallet\Contracts\TaxMinimalInterface;

class ItemMinTax extends Item implements TaxMinimalInterface
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
