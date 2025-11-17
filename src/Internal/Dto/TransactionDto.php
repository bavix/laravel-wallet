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

    /**
     * @return non-empty-string
     */
    public function getUuid(): string
    {
        /** @var non-empty-string $uuid */
        $uuid = $this->uuid;

        return $uuid;
    }

    /**
     * @return class-string
     */
    public function getPayableType(): string
    {
        /** @var class-string $payableType */
        $payableType = $this->payableType;

        return $payableType;
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

    /**
     * @return float|int|non-empty-string
     */
    public function getAmount(): float|int|string
    {
        /** @var float|int|non-empty-string $amount */
        $amount = $this->amount;

        return $amount;
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
