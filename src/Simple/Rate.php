<?php

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Interfaces\Wallet;

/**
 * Class Rate.
 */
class Rate implements Rateable
{
    /**
     * @var string
     */
    protected $amount;

    /**
     * @var Wallet|\Bavix\Wallet\Models\Wallet
     */
    protected $withCurrency;

    /**
     * {@inheritdoc}
     */
    public function withAmount($amount): Rateable
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withCurrency(Wallet $wallet): Rateable
    {
        $this->withCurrency = $wallet;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function convertTo(Wallet $wallet)
    {
        return $this->amount;
    }
}
