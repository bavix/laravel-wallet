<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\ExchangeInterface;

class ExchangeService implements ExchangeInterface
{
    public function convertTo(string $fromCurrency, string $toCurrency, $amount): string
    {
        return (string) $amount;
    }
}
