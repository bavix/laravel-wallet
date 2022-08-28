<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

/**
 * Currency exchange contract between wallets.
 */
interface ExchangeServiceInterface
{
    /**
     * Currency conversion method.
     */
    public function convertTo(string $fromCurrency, string $toCurrency, float|int|string $amount): string;
}
