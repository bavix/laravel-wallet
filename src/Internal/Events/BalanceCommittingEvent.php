<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

final readonly class BalanceCommittingEvent implements BalanceCommittingEventInterface
{
    /**
     * @param array<int, string> $balances
     * @param array<int, array{uuid: string, frozen_balance: string}> $walletStates
     */
    public function __construct(
        private array $balances,
        private array $walletStates,
    ) {
    }

    public function getBalances(): array
    {
        return $this->balances;
    }

    public function getWalletStates(): array
    {
        return $this->walletStates;
    }
}
