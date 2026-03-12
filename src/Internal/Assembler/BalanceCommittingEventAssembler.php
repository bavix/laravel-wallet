<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\BalanceCommittingEvent;
use Bavix\Wallet\Internal\Events\BalanceCommittingEventInterface;

final readonly class BalanceCommittingEventAssembler implements BalanceCommittingEventAssemblerInterface
{
    public function create(array $balances, array $walletsById): BalanceCommittingEventInterface
    {
        $walletStates = [];
        foreach ($walletsById as $walletId => $wallet) {
            $frozenValue = $wallet->getAttribute('frozen_balance');
            $walletStates[$walletId] = [
                'uuid' => $wallet->uuid,
                'frozen_balance' => is_string($frozenValue) ? $frozenValue : '0',
            ];
        }

        return new BalanceCommittingEvent($balances, $walletStates);
    }
}
