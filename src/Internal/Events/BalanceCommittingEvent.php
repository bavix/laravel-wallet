<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

final readonly class BalanceCommittingEvent implements BalanceCommittingEventInterface
{
    /**
     * @param array<int, string> $balances
     * @param array<int, array{uuid: string, attributes: array<string, mixed>}> $walletSnapshots
     */
    public function __construct(
        private array $balances,
        private array $walletSnapshots,
    ) {
    }

    public function getBalances(): array
    {
        return $this->balances;
    }

    public function getWalletSnapshots(): array
    {
        return $this->walletSnapshots;
    }
}
