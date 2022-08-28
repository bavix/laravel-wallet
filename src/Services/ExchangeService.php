<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

/**
 * @internal
 */
final class ExchangeService implements ExchangeServiceInterface
{
    public function convertTo(string $fromCurrency, string $toCurrency, float|int|string $amount): string
    {
        return (string) $amount;
    }
}
