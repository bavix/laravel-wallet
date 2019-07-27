<?php

namespace Bavix\Wallet\Interfaces;

interface MinimalTaxable extends Taxable
{
    /**
     * @return int
     */
    public function getMinimalFee(): int;
}
