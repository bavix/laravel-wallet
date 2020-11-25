<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface MinimalTaxable extends Taxable
{
    /**
     * @return int|float
     */
    public function getMinimalFee();
}
