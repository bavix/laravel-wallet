<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

interface TransactionCommittingEventInterface extends EventInterface
{
    /**
     * @return array<string, array{id: int, amount: string}>
     */
    public function getTransactions(): array;

    /**
     * @return array<int, string>
     */
    public function getFinalBalances(): array;
}
