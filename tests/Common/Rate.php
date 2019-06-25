<?php

namespace Bavix\Wallet\Test\Common;

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
     * Rate constructor.
     */
    public function __construct()
    {
        foreach ($this->rates as $from => $rates) {
            foreach ($rates as $to => $rate) {
                if (empty($this->rates[$to][$from])) {
                    $this->rates[$to][$from] = 1 / $rate;
                }
            }
        }
    }

    /**
     * @param Wallet $wallet
     * @return float
     */
    protected function rate(Wallet $wallet): float
    {
        /**
         * @var \Bavix\Wallet\Models\Wallet $wallet
         */
        return Arr::get(
            Arr::get($this->rates, $this->withCurrency->slug, []),
            $wallet->slug,
            1
        );
    }

    /**
     * @inheritDoc
     */
    public function convertTo(Wallet $wallet): float
    {
        return parent::convertTo($wallet) * $this->rate($wallet);
    }

}
