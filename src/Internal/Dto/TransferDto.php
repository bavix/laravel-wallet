<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use DateTimeImmutable;

/** @psalm-immutable */
final class TransferDto implements TransferDtoInterface
{
    private string $uuid;

    private int $depositId;
    private int $withdrawId;

    private string $status;

    private string $fromType;
    private int $fromId;

    private string $toType;
    private int $toId;

    private int $discount;
    private string $fee;

    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $uuid,
        int $depositId,
        int $withdrawId,
        string $status,
        string $fromType,
        int $fromId,
        string $toType,
        int $toId,
        int $discount,
        string $fee
    ) {
        $this->uuid = $uuid;
        $this->depositId = $depositId;
        $this->withdrawId = $withdrawId;
        $this->status = $status;
        $this->fromType = $fromType;
        $this->fromId = $fromId;
        $this->toType = $toType;
        $this->toId = $toId;
        $this->discount = $discount;
        $this->fee = $fee;
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

    public function getFromId(): int
    {
        return $this->fromId;
    }

    public function getToType(): string
    {
        return $this->toType;
    }

    public function getToId(): int
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
