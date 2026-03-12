<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

final readonly class TransactionCommittingEvent implements TransactionCommittingEventInterface
{
    /**
     * @param array<string, array{id: int, amount: string}> $transactions
     * @param array<int, string> $finalBalances
     */
    public function __construct(
        private array $transactions,
        private array $finalBalances,
    ) {
    }

    public function getTransactions(): array
    {
        return $this->transactions;
    }

    public function getFinalBalances(): array
    {
        return $this->finalBalances;
    }
}
