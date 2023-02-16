<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use DateTimeImmutable;

/** @immutable */
final class TransactionDto implements TransactionDtoInterface
{
    private readonly DateTimeImmutable $createdAt;

    private readonly DateTimeImmutable $updatedAt;

    /**
     * @param array<mixed>|null $meta
     */
    public function __construct(
        private readonly string $uuid,
        private readonly string $payableType,
        private readonly int|string $payableId,
        private readonly int $walletId,
        private readonly string $type,
        private readonly float|int|string $amount,
        private readonly bool $confirmed,
        private readonly ?array $meta
    ) {
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

    public function getPayableId(): int|string
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

    public function getAmount(): float|int|string
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
