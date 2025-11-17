<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use DateTimeImmutable;

final readonly class WalletCreatedEvent implements WalletCreatedEventInterface
{
    public function __construct(
        private string $holderType,
        private int|string $holderId,
        private string $walletUuid,
        private int $walletId,
        private DateTimeImmutable $createdAt
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

    /**
     * @return non-empty-string
     */
    public function getWalletUuid(): string
    {
        /** @var non-empty-string $walletUuid */
        $walletUuid = $this->walletUuid;

        return $walletUuid;
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
