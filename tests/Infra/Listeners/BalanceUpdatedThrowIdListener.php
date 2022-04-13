<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Listeners;

use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Test\Infra\Exceptions\UnknownEventException;

final class BalanceUpdatedThrowIdListener
{
    public function handle(BalanceUpdatedEventInterface $balanceChangedEvent): void
    {
        throw new UnknownEventException(
            (string) $balanceChangedEvent->getWalletId(),
            (int) $balanceChangedEvent->getBalance()
        );
    }
}
