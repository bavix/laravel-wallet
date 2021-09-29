<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transfer;

interface Exchangeable
{
    /**
     * @param int|string $amount
     */
    public function exchange(Wallet $to, $amount, ?array $meta = null): Transfer;

    /**
     * @param int|string $amount
     */
    public function safeExchange(Wallet $to, $amount, ?array $meta = null): ?Transfer;

    /**
     * @param int|string $amount
     */
    public function forceExchange(Wallet $to, $amount, ?array $meta = null): Transfer;
}
