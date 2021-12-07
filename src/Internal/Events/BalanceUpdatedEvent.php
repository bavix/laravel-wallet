<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use DateTimeImmutable;

final class BalanceUpdatedEvent implements BalanceUpdatedEventInterface
{
    private int $walletId;
    private string $walletUuid;
    private string $balance;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        int $walletId,
        string $walletUuid,
        string $balance,
        DateTimeImmutable $updatedAt
    ) {
        $this->walletId = $walletId;
        $this->walletUuid = $walletUuid;
        $this->balance = $balance;
        $this->updatedAt = $updatedAt;
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
