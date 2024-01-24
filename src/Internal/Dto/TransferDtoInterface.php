<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use DateTimeImmutable;

interface TransferDtoInterface
{
    public function getUuid(): string;

    public function getDepositId(): int;

    public function getWithdrawId(): int;

    public function getStatus(): string;

    public function getFromId(): int|string;

    public function getToId(): int|string;

    public function getDiscount(): int;

    public function getFee(): string;

    /** @return array<mixed>|null */
    public function getExtra(): ?array;

    public function getCreatedAt(): DateTimeImmutable;

    public function getUpdatedAt(): DateTimeImmutable;
}
