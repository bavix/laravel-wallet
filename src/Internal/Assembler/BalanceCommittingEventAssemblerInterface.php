<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\BalanceCommittingEventInterface;
use Bavix\Wallet\Models\Wallet;

interface BalanceCommittingEventAssemblerInterface
{
    /**
     * @param array<int, string> $balances
     * @param array<int, Wallet> $walletsById
     */
    public function create(array $balances, array $walletsById): BalanceCommittingEventInterface;
}
