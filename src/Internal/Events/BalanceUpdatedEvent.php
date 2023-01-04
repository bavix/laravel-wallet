<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use DateTimeImmutable;

final class BalanceUpdatedEvent implements BalanceUpdatedEventInterface
{
    public function __construct(
        private readonly int $walletId,
        private readonly string $walletUuid,
        private readonly string $balance,
        private readonly DateTimeImmutable $updatedAt
    ) {
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function getWalletUuid(): string
    {
        return $this->walletUuid;
    }

    public function getBalance(): string
    {
        return $this->balance;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
