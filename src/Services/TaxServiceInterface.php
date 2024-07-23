<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;

/**
 * @api
 */
interface TaxServiceInterface
{
    /**
     * Calculates the fee for a given wallet and amount.
     *
     * This method calculates the fee for a given wallet and amount.
     * The fee is determined by the wallet's getFeePercent() method.
     * The amount is then multiplied by the fee percentage and divided by 100.
     * The result is then rounded to the wallet's decimal places and formatted as a string.
     *
     * @param Wallet $wallet The wallet to calculate the fee for.
     * @param float|int|string $amount The amount to calculate the fee for.
     * @return string The fee, formatted as a string with the same decimal places as the wallet.
     *
     * @see \Bavix\Wallet\Interfaces\Taxable::getFeePercent()
     */
    public function getFee(Wallet $wallet, float|int|string $amount): string;
}
