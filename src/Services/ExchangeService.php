<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;

class ExchangeService
{

    /**
     * @param Wallet $from
     * @param Wallet $to
     * @return float
     */
    public function rate(Wallet $from, Wallet $to): float
    {
        return 1.0;
    }

}
