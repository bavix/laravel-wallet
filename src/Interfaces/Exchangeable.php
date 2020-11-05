<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transfer;

interface Exchangeable
{
    /**
     * @param Wallet $to
     * @param int|string $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function exchange(Wallet $to, $amount, ?array $meta = null): Transfer;

    /**
     * @param Wallet $to
     * @param int|string $amount
     * @param array|null $meta
     * @return Transfer|null
     */
    public function safeExchange(Wallet $to, $amount, ?array $meta = null): ?Transfer;

    /**
     * @param Wallet $to
     * @param int|string $amount
     * @param array|null $meta
     * @return Transfer
     */
    public function forceExchange(Wallet $to, $amount, ?array $meta = null): Transfer;
}
