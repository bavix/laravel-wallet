<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface MinimalTaxable extends Taxable
{
    /** @return float|int|string */
    public function getMinimalFee();
}
