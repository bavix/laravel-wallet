<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

final class ExchangeService implements ExchangeServiceInterface
{
    /** @param float|int|string $amount */
    public function convertTo(string $fromCurrency, string $toCurrency, $amount): string
    {
        return (string) $amount;
    }
}
