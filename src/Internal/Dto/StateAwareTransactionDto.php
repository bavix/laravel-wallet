<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Enums\TransactionType;
use DateTimeImmutable;

final readonly class StateAwareTransactionDto implements StateAwareTransactionDtoInterface
{
    public function __construct(
        private TransactionDtoInterface $dto,
        private string $balanceBefore,
        private string $balanceAfter,
    ) {
    }

    public function getUuid(): string
    {
        return $this->dto->getUuid();
    }

    public function getPayableType(): string
    {
        return $this->dto->getPayableType();
    }

    public function getPayableId(): int|string
    {
        return $this->dto->getPayableId();
    }

    public function getWalletId(): int
    {
        return $this->dto->getWalletId();
    }

    public function getType(): TransactionType
    {
        return $this->dto->getType();
    }

    public function getAmount(): float|int|string
    {
        return $this->dto->getAmount();
    }

    public function isConfirmed(): bool
    {
        return $this->dto->isConfirmed();
    }

    public function getMeta(): ?array
    {
        return $this->dto->getMeta();
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->dto->getCreatedAt();
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->dto->getUpdatedAt();
    }

    public function getBalanceBefore(): string
    {
        return $this->balanceBefore;
    }

    public function getBalanceAfter(): string
    {
        return $this->balanceAfter;
    }
}
