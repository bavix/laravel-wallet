<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Services;

use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Services\ExchangeServiceInterface;

final class ExchangeUsdToBtcService implements ExchangeServiceInterface
{
    /**
     * @var array<string, array<string, float>>
     */
    private array $rates = [
        'USD' => [
            'BTC' => 0.004636,
        ],
    ];

    public function __construct(
        private readonly MathServiceInterface $mathService
    ) {
    }

    public function convertTo(string $fromCurrency, string $toCurrency, float|int|string $amount): string
    {
        return $this->mathService->mul($amount, $this->rates[$fromCurrency][$toCurrency] ?? 1.);
    }
}
