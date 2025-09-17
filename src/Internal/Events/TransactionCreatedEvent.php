<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use Bavix\Wallet\Enums\TransactionType;
use DateTimeImmutable;

final readonly class TransactionCreatedEvent implements TransactionCreatedEventInterface
{
    public function __construct(
        private int $id,
        private TransactionType $type,
        private int $walletId,
        private DateTimeImmutable $createdAt,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getType(): TransactionType
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
