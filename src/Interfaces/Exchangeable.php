<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transfer;

interface Exchangeable
{
    /**
     * @param Wallet $to
     * @param int $amount
     * @return Transfer
     */
    public function exchange(Wallet $to, int $amount): Transfer;

    /**
     * @param Wallet $to
     * @param int $amount
     * @return Transfer|null
     */
    public function safeExchange(Wallet $to, int $amount): ?Transfer;

    /**
     * @param Wallet $to
     * @param int $amount
     * @return Transfer
     */
    public function forceExchange(Wallet $to, int $amount): Transfer;
}
