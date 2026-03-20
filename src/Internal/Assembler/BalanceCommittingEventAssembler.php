<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\BalanceCommittingEvent;
use Bavix\Wallet\Internal\Events\BalanceCommittingEventInterface;

final readonly class BalanceCommittingEventAssembler implements BalanceCommittingEventAssemblerInterface
{
    public function create(array $balances, array $walletsById): BalanceCommittingEventInterface
    {
        $walletSnapshots = [];
        foreach ($walletsById as $walletId => $wallet) {
            $walletSnapshots[$walletId] = [
                'uuid' => $wallet->uuid,
                'attributes' => $wallet->getAttributes(),
            ];
        }

        return new BalanceCommittingEvent($balances, $walletSnapshots);
    }
}
