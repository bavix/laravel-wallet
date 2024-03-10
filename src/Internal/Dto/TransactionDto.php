<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use DateTimeImmutable;

/** @immutable */
final readonly class TransactionDto implements TransactionDtoInterface
{
    /**
     * @param array<mixed>|null $meta
     */
    public function __construct(
        private string $uuid,
        private string $payableType,
        private int|string $payableId,
        private int $walletId,
        private string $type,
        private float|int|string $amount,
        private bool $confirmed,
        private ?array $meta,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {
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
