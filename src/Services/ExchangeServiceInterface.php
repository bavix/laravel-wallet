<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

/**
 * Currency exchange contract between wallets.
 *
 * @api
 */
interface ExchangeServiceInterface
{
    /**
     * Performs a currency conversion from the specified source currency to the target currency.
     *
     * @param string $fromCurrency The source currency code.
     * @param string $toCurrency The target currency code.
     * @param float|int|string $amount The amount to be converted.
     * @return string The converted amount.
     */
    public function convertTo(string $fromCurrency, string $toCurrency, float|int|string $amount): string;
}
