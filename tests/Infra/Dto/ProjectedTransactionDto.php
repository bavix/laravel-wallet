<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Dto;

use Bavix\Wallet\Enums\TransactionType;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use DateTimeImmutable;

final readonly class ProjectedTransactionDto implements TransactionDtoInterface
{
    /**
     * @param array<mixed>|null $meta
     */
    public function __construct(
        private TransactionDtoInterface $inner,
        private ?array $meta,
    ) {
    }

    public function getUuid(): string
    {
        return $this->inner->getUuid();
    }

    public function getPayableType(): string
    {
        return $this->inner->getPayableType();
    }

    public function getPayableId(): int|string
    {
        return $this->inner->getPayableId();
    }

    public function getWalletId(): int
    {
        return $this->inner->getWalletId();
    }

    public function getType(): TransactionType
    {
        return $this->inner->getType();
    }

    public function getAmount(): float|int|string
    {
        return $this->inner->getAmount();
    }

    public function isConfirmed(): bool
    {
        return $this->inner->isConfirmed();
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->inner->getCreatedAt();
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->inner->getUpdatedAt();
    }
}
