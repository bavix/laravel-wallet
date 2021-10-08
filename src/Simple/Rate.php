<?php

namespace Bavix\Wallet\Simple;

use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\ExchangeInterface;

/**
 * Class Rate.
 *
 * @deprecated Not used anymore
 * @see Exchange
 */
class Rate implements Rateable
{
    protected string $amount;

    protected Wallet $withCurrency;

    private ExchangeInterface $exchange;

    public function __construct(ExchangeInterface $exchange)
    {
        $this->exchange = $exchange;
    }

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
        /** @var \Bavix\Wallet\Models\Wallet $wallet */
        return $this->exchange->convertTo(
            $this->withCurrency->currency,
            $wallet->currency,
            $this->amount
        );
    }
}
