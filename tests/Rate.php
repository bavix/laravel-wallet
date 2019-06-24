<?php

namespace Bavix\Wallet\Test;

use Bavix\Wallet\Interfaces\Wallet;
use Illuminate\Support\Arr;

class Rate extends \Bavix\Wallet\Simple\Rate
{

    /**
     * @var array
     */
    protected $rates = [
        'usd' => [
            'rub' => 67.61,
        ],
    ];

    /**
     * @param Wallet $wallet
     * @return float
     */
    protected function rate(Wallet $wallet): float
    {
        $from = $this->withCurrency->slug;
        $to = $wallet->slug;

        if (Arr::has($this->rates, "$from.$to")) {
            return Arr::get($this->rates, "$from.$to");
        }

        if (Arr::has($this->rates, "$to.$from")) {
            return 1 / Arr::get($this->rates, "$to.$from");
        }

        return 1;
    }

    /**
     * @inheritDoc
     */
    public function convertTo(Wallet $wallet): float
    {
        return $this->amount * $this->rate($wallet);
    }

}
