<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\RateInterface;

class RateService implements RateInterface
{
    public function convertTo(
        string $fromCurrency,
        string $toCurrency,
        $amount
    ): string {
        return (string) $amount;
    }
}
