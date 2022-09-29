<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transaction;

interface TransactionHandlerInterface
{
    /**
     * Should be used for high-load pens that change the balance of many wallets at the same time.
     *
     * Attention! confirmed by default TRUE.
     *
     * @param non-empty-array<array{
     *     wallet: Wallet,
     *     amount: float|int|string,
     *     meta?: ?array<mixed>,
     *     confirmed?: bool
     * }> $objects
     *
     * @return non-empty-array<string, Transaction>
     */
    public function apply(array $objects): array;
}
