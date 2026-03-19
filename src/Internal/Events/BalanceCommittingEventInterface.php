<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

interface BalanceCommittingEventInterface extends EventInterface
{
    /**
     * @return array<int, string>
     */
    public function getBalances(): array;

    /**
     * @return array<int, array{uuid: string, attributes: array<string, mixed>}>
     */
    public function getWalletSnapshots(): array;
}
