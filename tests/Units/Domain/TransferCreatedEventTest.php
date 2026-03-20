<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Units\Domain;

use Bavix\Wallet\Enums\TransferStatus;
use Bavix\Wallet\Internal\Events\TransferCreatedEvent;
use Bavix\Wallet\Test\Infra\TestCase;
use DateTimeImmutable;

/**
 * @internal
 */
final class TransferCreatedEventTest extends TestCase
{
    public function testGetters(): void
    {
        $createdAt = new DateTimeImmutable();
        $event = new TransferCreatedEvent(10, 20, 30, TransferStatus::Paid, $createdAt);

        self::assertSame(10, $event->getTransferId());
        self::assertSame(20, $event->getFromWalletId());
        self::assertSame(30, $event->getToWalletId());
        self::assertSame(TransferStatus::Paid, $event->getStatus());
        self::assertSame($createdAt, $event->getCreatedAt());
    }
}
