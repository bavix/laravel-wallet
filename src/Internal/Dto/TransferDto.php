<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use DateTimeImmutable;

/** @immutable */
final class TransferDto implements TransferDtoInterface
{
    private readonly DateTimeImmutable $createdAt;

    private readonly DateTimeImmutable $updatedAt;

    public function __construct(
        private readonly string $uuid,
        private readonly int $depositId,
        private readonly int $withdrawId,
        private readonly string $status,
        private readonly string $fromType,
        private readonly int|string $fromId,
        private readonly string $toType,
        private readonly int|string $toId,
        private readonly int $discount,
        private readonly string $fee
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

    public function getFromType(): string
    {
        return $this->fromType;
    }

    public function getFromId(): int|string
    {
        return $this->fromId;
    }

    public function getToType(): string
    {
        return $this->toType;
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
