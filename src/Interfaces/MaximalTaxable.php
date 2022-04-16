<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface MaximalTaxable extends Taxable
{
    /**
     * @return float|int
     */
    public function getMaximalFee();
}
