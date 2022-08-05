<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Listeners;

use Bavix\Wallet\Internal\Events\TransactionCreatedEventInterface;
use Bavix\Wallet\Test\Infra\Exceptions\UnknownEventException;

final class TransactionCreatedThrowListener
{
    public function handle(TransactionCreatedEventInterface $transactionCreatedEvent): void
    {
        $type = $transactionCreatedEvent->getType();
        $walletId = $transactionCreatedEvent->getWalletId();
        $createdAt = $transactionCreatedEvent->getCreatedAt()
            ->format(\DateTimeInterface::ATOM)
        ;

        $message = hash('sha256', $type . $walletId . $createdAt);

        throw new UnknownEventException($message, $transactionCreatedEvent->getId());
    }
}
