<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use DateTimeImmutable;

/** @psalm-immutable */
final class TransferDto implements TransferDtoInterface
{
    private DateTimeImmutable $createdAt;

    private DateTimeImmutable $updatedAt;

    public function __construct(
        private string $uuid,
        private int $depositId,
        private int $withdrawId,
        private string $status,
        private string $fromType,
        private int|string $fromId,
        private string $toType,
        private int|string $toId,
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
