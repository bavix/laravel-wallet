<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Listeners;

use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Internal\Exceptions\UnknownEventException;

final class BalanceUpdatedThrowUuidListener
{
    public function handle(BalanceUpdatedEventInterface $balanceChangedEvent): void
    {
        throw new UnknownEventException($balanceChangedEvent->getWalletUuid(), (int) $balanceChangedEvent->getBalance());
    }
}
