<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

use Bavix\Wallet\Models\Transfer;

interface ExchangeInterface
{
    /**
     * @param int|string $amount
     */
    public function exchange(WalletInterface $to, $amount, ?array $meta = null): Transfer;

    /**
     * @param int|string $amount
     */
    public function safeExchange(WalletInterface $to, $amount, ?array $meta = null): ?Transfer;

    /**
     * @param int|string $amount
     */
    public function forceExchange(WalletInterface $to, $amount, ?array $meta = null): Transfer;
}
