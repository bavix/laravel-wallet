<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

/**
 * @deprecated Use TaxConstraintsInterface.
 * @see TaxConstraintsInterface
 */
interface MaximalTaxable extends Taxable
{
    /**
     * Returns the maximum fee that can be charged for a payment.
     *
     * This method returns the maximum fee that can be charged for a payment.
     * The return value can be either a float or an integer.
     *
     * For example, if the commission is 50% and the upper limit is $10,
     * then for $100 the commission will be $10.
     *
     * @return float|int The maximum fee that can be charged for a payment.
     *
     * @see TaxService::getFee()
     */
    public function getMaximalFee(): float|int;
}
