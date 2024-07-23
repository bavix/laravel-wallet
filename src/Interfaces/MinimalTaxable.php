<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

interface MinimalTaxable extends Taxable
{
    /**
     * Returns the minimum fee for a transaction.
     *
     * The minimum fee specifies the minimum amount of money that should be charged for a transaction.
     * It can be either a fixed amount or a percentage of the transaction amount.
     * The fee can be a float or integer.
     *
     * @return float|int The minimum fee for the transaction.
     *
     * @example
     *
     * If the transaction amount is $100 and the fee is 1% and the minimal fee is $10,
     * then the minimum fee for the transaction will be $10.
     *
     * If the transaction amount is $100 and the fee is 2% and the minimal fee is $5,
     * then the minimum fee for the transaction will be $5.
     *
     * @see \Bavix\Wallet\Services\TaxService::getFee()
     */
    public function getMinimalFee(): float|int;
}
