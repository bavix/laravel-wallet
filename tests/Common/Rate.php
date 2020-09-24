<?php

namespace Bavix\Wallet\Test\Common;

use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Services\WalletService;
use Illuminate\Support\Arr;

class Rate extends \Bavix\Wallet\Simple\Rate
{
    /**
     * @var array
     */
    protected $rates = [
        'USD' => [
            'RUB' => 67.61,
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
                    $this->rates[$to][$from] = app(Mathable::class)->div(1, $rate);
                }
            }
        }
    }

    /**
     * @param Wallet $wallet
     * @return int|float
     */
    protected function rate(Wallet $wallet)
    {
        $from = app(WalletService::class)->getWallet($this->withCurrency);
        $to = app(WalletService::class)->getWallet($wallet);

        /**
         * @var \Bavix\Wallet\Models\Wallet $wallet
         */
        return Arr::get(
            Arr::get($this->rates, $from->currency, []),
            $to->currency,
            1
        );
    }

    /**
     * {@inheritdoc}
     */
    public function convertTo(Wallet $wallet)
    {
        return app(Mathable::class)->mul(
            parent::convertTo($wallet),
            $this->rate($wallet)
        );
    }
}
