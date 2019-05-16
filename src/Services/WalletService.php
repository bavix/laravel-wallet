<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Taxing;
use Bavix\Wallet\Interfaces\Wallet;

class WalletService
{

    /**
     * Consider the fee that the system will receive.
     *
     * @param Wallet $wallet
     * @param int $amount
     * @return int
     */
    public function fee(Wallet $wallet, int $amount): int
    {
        if ($wallet instanceof Taxing) {
            return (int)($amount * $wallet->getFeePercent() / 100);
        }

        return 0;
    }

}
