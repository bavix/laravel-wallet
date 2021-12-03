<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use DateTimeImmutable;

final class WalletCreatedEvent implements WalletCreatedEventInterface
{
    private string $holderType;
    private int $holderId;
    private string $walletUuid;
    private int $walletId;
    private DateTimeImmutable $createdAt;

    public function __construct(
        string $holderType,
        int $holderId,
        string $walletUuid,
        int $walletId,
        DateTimeImmutable $createdAt
    ) {
        $this->holderType = $holderType;
        $this->holderId = $holderId;
        $this->walletUuid = $walletUuid;
        $this->walletId = $walletId;
        $this->createdAt = $createdAt;
    }

    public function getHolderType(): string
    {
        return $this->holderType;
    }

    public function getHolderId(): int
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
