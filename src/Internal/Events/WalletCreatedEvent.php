<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use DateTimeImmutable;

final class WalletCreatedEvent implements WalletCreatedEventInterface
{
    public function __construct(
        private readonly string $holderType,
        private readonly int|string $holderId,
        private readonly string $walletUuid,
        private readonly int $walletId,
        private readonly DateTimeImmutable $createdAt
    ) {
    }

    public function getHolderType(): string
    {
        return $this->holderType;
    }

    public function getHolderId(): int|string
    {
        return $this->holderId;
    }

    public function getWalletUuid(): string
    {
        return $this->walletUuid;
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
