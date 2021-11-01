<?php

namespace Bavix\Wallet\Test\Common;

use Bavix\Wallet\Internal\ExchangeInterface;
use Bavix\Wallet\Internal\MathInterface;
use Illuminate\Support\Arr;

class MyExchange implements ExchangeInterface
{
    private array $rates = [
        'USD' => [
            'RUB' => 67.61,
        ],
    ];

    private MathInterface $math;

    /**
     * Rate constructor.
     */
    public function __construct(MathInterface $mathService)
    {
        $this->math = $mathService;

        foreach ($this->rates as $from => $rates) {
            foreach ($rates as $to => $rate) {
                if (empty($this->rates[$to][$from])) {
                    $this->rates[$to][$from] = $this->math->div(1, $rate);
                }
            }
        }
    }

    /** @param float|int|string $amount */
    public function convertTo(string $fromCurrency, string $toCurrency, $amount): string
    {
        return $this->math->mul($amount, Arr::get(
            Arr::get($this->rates, $fromCurrency, []),
            $toCurrency,
            1
        ));
    }
}
