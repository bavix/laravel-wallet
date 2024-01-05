<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use DateTimeImmutable;

/** @immutable */
final readonly class TransferDto implements TransferDtoInterface
{
    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(
        private string $uuid,
        private int $depositId,
        private int $withdrawId,
        private string $status,
        private int $fromId,
        private int $toId,
        private int $discount,
        private string $fee
    ) {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getDepositId(): int
    {
        return $this->depositId;
    }

    public function getWithdrawId(): int
    {
        return $this->withdrawId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getFromId(): int|string
    {
        return $this->fromId;
    }

    public function getToId(): int|string
    {
        return $this->toId;
    }

    public function getDiscount(): int
    {
        return $this->discount;
    }

    public function getFee(): string
    {
        return $this->fee;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
