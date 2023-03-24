<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Services;

use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Services\ExchangeServiceInterface;

final class MyExchangeService implements ExchangeServiceInterface
{
    /**
     * @var array<string, array<string, int|float|string>>
     */
    private array $rates = [
        'USD' => [
            'RUB' => 67.61,
        ],
    ];

    public function __construct(
        private readonly MathServiceInterface $mathService
    ) {
        foreach ($this->rates as $from => $rates) {
            foreach ($rates as $to => $rate) {
                $this->rates[$to][$from] ??= $this->mathService->div(1, $rate);
            }
        }
    }

    public function convertTo(string $fromCurrency, string $toCurrency, float|int|string $amount): string
    {
        return $this->mathService->mul($amount, $this->rates[$fromCurrency][$toCurrency] ?? 1.);
    }
}
