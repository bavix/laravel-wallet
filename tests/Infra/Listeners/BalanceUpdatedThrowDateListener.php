<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Listeners;

use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Test\Infra\Exceptions\UnknownEventException;
use DateTimeInterface;

final class BalanceUpdatedThrowDateListener
{
    public function handle(BalanceUpdatedEventInterface $balanceChangedEvent): void
    {
        throw new UnknownEventException(
            $balanceChangedEvent->getUpdatedAt()
                ->format(DateTimeInterface::ATOM),
            (int) $balanceChangedEvent->getBalance()
        );
    }
}
