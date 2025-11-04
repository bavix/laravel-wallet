<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Events;

use Bavix\Wallet\Enums\TransferStatus;
use DateTimeImmutable;

final readonly class TransferCreatedEvent implements TransferCreatedEventInterface
{
    public function __construct(
        private int $transferId,
        private int $fromWalletId,
        private int $toWalletId,
        private TransferStatus $status,
        private DateTimeImmutable $createdAt
    ) {
    }

    public function getTransferId(): int
    {
        return $this->transferId;
    }

    public function getFromWalletId(): int
    {
        return $this->fromWalletId;
    }

    public function getToWalletId(): int
    {
        return $this->toWalletId;
    }

    public function getStatus(): TransferStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
