<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use DateTimeImmutable;

interface TransactionDtoInterface
{
    public function getUuid(): string;

    public function getPayableType(): string;

    public function getPayableId(): int;

    public function getWalletId(): int;

    public function getType(): string;

    public function getAmount(): string;

    public function isConfirmed(): bool;

    public function getMeta(): ?array;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;
}
