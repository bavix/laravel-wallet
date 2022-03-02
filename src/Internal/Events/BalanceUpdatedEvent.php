<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use DateTimeImmutable;

final class BalanceUpdatedEvent implements BalanceUpdatedEventInterface
{
    public function __construct(
        private int $walletId,
        private string $walletUuid,
        private string $balance,
        private DateTimeImmutable $updatedAt
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
