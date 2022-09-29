<?php

declare(strict_types=1);

namespace Bavix\Wallet\External\Api;

use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Models\Transfer;

interface TransferHandlerInterface
{
    /**
     * To ensure high performance, there is no check for a valid array inside the handles.
     * I recommend using static analysis so as not to miss an error.
     *
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
     *
     * @throws ExceptionInterface
     */
    public function apply(array $objects): array;
}
