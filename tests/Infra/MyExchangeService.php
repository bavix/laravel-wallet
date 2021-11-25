<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra;

use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Services\ExchangeServiceInterface;

class MyExchangeService implements ExchangeServiceInterface
{
    private MathServiceInterface $mathService;

    private array $rates = [
        'USD' => [
            'RUB' => 67.61,
        ],
    ];

    /**
     * Rate constructor.
     */
    public function __construct(MathServiceInterface $mathService)
    {
        $this->mathService = $mathService;

        foreach ($this->rates as $from => $rates) {
            foreach ($rates as $to => $rate) {
                if (empty($this->rates[$to][$from])) {
                    $this->rates[$to][$from] = $this->mathService->div(1, $rate);
                }
            }
        }
    }

    /** @param float|int|string $amount */
    public function convertTo(string $fromCurrency, string $toCurrency, $amount): string
    {
        return $this->mathService->mul($amount, $this->rates[$fromCurrency][$toCurrency] ?? 1);
    }
}
