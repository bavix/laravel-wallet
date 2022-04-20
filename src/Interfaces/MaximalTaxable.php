<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface MaximalTaxable extends Taxable
{
    public function getMaximalFee(): float|int;
}
