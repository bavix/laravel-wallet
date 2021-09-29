<?php

declare(strict_types=1);

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Internal\ExchangeInterface;

class Exchange implements ExchangeInterface
{
    public function convertTo(string $fromCurrency, string $toCurrency, $amount): string
    {
        return (string) $amount;
    }
}
