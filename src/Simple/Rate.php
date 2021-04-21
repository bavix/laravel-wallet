<?php

declare(strict_types=1);

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Interfaces\Wallet;

class Rate implements Rateable
{
    protected string $amount;

    protected Wallet $withCurrency;

    public function withAmount($amount): Rateable
    {
        $this->amount = (string) $amount;

        return $this;
    }

    public function withCurrency(Wallet $wallet): Rateable
    {
        $this->withCurrency = $wallet;

        return $this;
    }

    public function convertTo(Wallet $wallet): string
    {
        return $this->amount;
    }
}
