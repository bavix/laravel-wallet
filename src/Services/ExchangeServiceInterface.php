<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

interface ExchangeServiceInterface
{
    public function convertTo(string $fromCurrency, string $toCurrency, float|int|string $amount): string;
}
