<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\ExchangeInterface;

/**
 * @deprecated
 * @see ExchangeInterface
 */
class ExchangeService
{
    private Rateable $rate;

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
