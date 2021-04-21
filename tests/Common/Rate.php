<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Common;

use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Services\WalletService;
use Illuminate\Support\Arr;

class Rate extends \Bavix\Wallet\Simple\Rate
{
    protected array $rates = [
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

    public function convertTo(Wallet $wallet): string
    {
        return app(Mathable::class)->mul(
            parent::convertTo($wallet),
            $this->rate($wallet)
        );
    }

    protected function rate(Wallet $wallet): string
    {
        $from = app(WalletService::class)->getWallet($this->withCurrency);
        $to = app(WalletService::class)->getWallet($wallet);

        // @var \Bavix\Wallet\Models\Wallet $wallet
        return (string) Arr::get(
            Arr::get($this->rates, $from->currency, []),
            $to->currency,
            1
        );
    }
}
