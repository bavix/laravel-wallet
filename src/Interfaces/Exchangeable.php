<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transfer;

interface Exchangeable
{
    public function exchange(Wallet $to, $amount, ?array $meta = null): Transfer;

    public function safeExchange(Wallet $to, $amount, ?array $meta = null): ?Transfer;

    public function forceExchange(Wallet $to, $amount, ?array $meta = null): Transfer;
}
