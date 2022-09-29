<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transfer;

interface TransferHandlerInterface
{
    /**
     * Should be used for high-load pens that change the balance of many wallets at the same time.
     *
     * @param non-empty-array<array{
     *     from: Wallet,
     *     to: Wallet,
     *     amount: float|int|string,
     *     meta?: ExtraDtoInterface|array<mixed>|null
     * }> $objects
     *
     * @return non-empty-array<string, Transfer>
     */
    public function apply(array $objects): array;
}
