<?php

namespace Bavix\Wallet\Test\Common;

use Bavix\Wallet\Internal\ExchangeInterface;
use Bavix\Wallet\Internal\MathInterface;
use Illuminate\Support\Arr;

class MyExchange implements ExchangeInterface
{
    private MathInterface $mathService;

    private array $rates = [
        'USD' => [
            'RUB' => 67.61,
        ],
    ];

    /**
     * Rate constructor.
     */
    public function __construct(MathInterface $mathService)
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
        return $this->mathService->mul($amount, Arr::get(
            Arr::get($this->rates, $fromCurrency, []),
            $toCurrency,
            1
        ));
    }
}
