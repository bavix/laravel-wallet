<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Projector;

use Bavix\Wallet\Models\Wallet;

interface WalletBatchProjectorInterface
{
    /**
     * @param non-empty-array<int, string> $balances
     * @param array<int, Wallet> $walletsById
     * @return array<int, array<string, null|bool|float|int|string>>
     */
    public function project(array $balances, array $walletsById): array;
}
