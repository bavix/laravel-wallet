<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

interface TaxMinimalInterface extends TaxInterface
{
    /**
     * @return float|int
     */
    public function getMinimalFee();
}
