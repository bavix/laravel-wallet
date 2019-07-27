<?php

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Interfaces\Wallet;

/**
 * Class Rate
 * @package Bavix\Wallet\Simple
 */
class Rate implements Rateable
{

    /**
     * @var int
     */
    protected $amount;

    /**
     * @var Wallet|\Bavix\Wallet\Models\Wallet
     */
    protected $withCurrency;

    /**
     * @inheritDoc
     */
    public function withAmount(int $amount): Rateable
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withCurrency(Wallet $wallet): Rateable
    {
        $this->withCurrency = $wallet;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function convertTo(Wallet $wallet): float
    {
        return $this->amount;
    }

}
