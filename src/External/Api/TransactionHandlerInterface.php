<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Models\Transaction;

interface TransactionHandlerInterface
{
    /**
     * To ensure high performance, there is no check for a valid array inside the handles.
     * I recommend using static analysis so as not to miss an error.
     *
     * Should be used for high-load pens that change the balance of many wallets at the same time.
     *
     * Attention! confirmed by default TRUE.
     *
     * @param non-empty-array<array{
     *     type: Transaction::TYPE_DEPOSIT|Transaction::TYPE_WITHDRAW,
     *     wallet: Wallet,
     *     amount: float|int|string,
     *     meta?: ?array<mixed>,
     *     confirmed?: bool
     * }> $objects
     *
     * @return non-empty-array<string, Transaction>
     *
     * @throws ExceptionInterface
     */
    public function apply(array $objects): array;
}
