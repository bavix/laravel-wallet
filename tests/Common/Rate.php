<?php

namespace Bavix\Wallet\Test\Common;

use Bavix\Wallet\Interfaces\ExchangeInterface;
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

    private $walletService;

    private $math;

    /**
     * Rate constructor.
     */
    public function __construct(ExchangeInterface $exchange, Mathable $math, WalletService $walletService)
    {
        parent::__construct($exchange);
        $this->walletService = $walletService;
        $this->math = $math;

        foreach ($this->rates as $from => $rates) {
            foreach ($rates as $to => $rate) {
                if (empty($this->rates[$to][$from])) {
                    $this->rates[$to][$from] = $this->math->div(1, $rate);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function convertTo(Wallet $wallet)
    {
        return $this->math->mul(
            parent::convertTo($wallet),
            $this->rate($wallet)
        );
    }

    /**
     * @return float|int
     */
    protected function rate(Wallet $wallet)
    {
        $from = $this->walletService->getWallet($this->withCurrency);
        $to = $this->walletService->getWallet($wallet);

        /**
         * @var \Bavix\Wallet\Models\Wallet $wallet
         */
        return Arr::get(
            Arr::get($this->rates, $from->currency, []),
            $to->currency,
            1
        );
    }
}
