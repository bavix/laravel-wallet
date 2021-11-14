<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use DateTimeImmutable;

/** @psalm-immutable */
final class TransactionDto implements TransactionDtoInterface
{
    private string $uuid;

    private string $payableType;
    private int $payableId;

    private int $walletId;

    private string $type;

    private string $amount;

    private bool $confirmed;

    private ?array $meta;

    private DateTimeImmutable $createdAt;
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $uuid,
        string $payableType,
        int $payableId,
        int $walletId,
        string $type,
        string $amount,
        bool $confirmed,
        ?array $meta
    ) {
        $this->uuid = $uuid;
        $this->payableType = $payableType;
        $this->payableId = $payableId;
        $this->walletId = $walletId;
        $this->type = $type;
        $this->amount = $amount;
        $this->confirmed = $confirmed;
        $this->meta = $meta;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getPayableType(): string
    {
        return $this->payableType;
    }

    public function getPayableId(): int
    {
        return $this->payableId;
    }

    public function getWalletId(): int
    {
        return $this->walletId;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
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
