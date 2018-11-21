<?php

namespace Bavix\Wallet;

use Bavix\Wallet\Interfaces\Taxing;
use Bavix\Wallet\Interfaces\Wallet;

class Tax
{

    /**
     * Consider the fee that the system will receive.
     *
     * @param Wallet $wallet
     * @param int $amount
     * @return int
     */
    public static function fee(Wallet $wallet, int $amount): int
    {
        if ($wallet instanceof Taxing) {
            return (int)($amount * $wallet->getFeePercent() / 100);
        }

        return 0;
    }

}
