<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Interfaces\Wallet;

class ExchangeService
{
    private $rate;

    public function __construct(Rateable $rate)
    {
        $this->rate = $rate;
    }

    /**
     * @return float|int
     */
    public function rate(Wallet $from, Wallet $to)
    {
        return $this->rate
            ->withAmount(1)
            ->withCurrency($from)
            ->convertTo($to)
        ;
    }
}
