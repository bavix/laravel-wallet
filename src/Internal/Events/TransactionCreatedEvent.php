<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use DateTimeImmutable;

final class TransactionCreatedEvent implements TransactionCreatedEventInterface
{
    public function __construct(
        private readonly int $id,
        private readonly string $type,
        private readonly int $walletId,
        private readonly DateTimeImmutable $createdAt,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
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
