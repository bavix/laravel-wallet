<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use DateTimeImmutable;

interface TransferDtoInterface
{
    /**
     * Get the UUID of the transfer.
     *
     * @return non-empty-string
     */
    public function getUuid(): string;

    /**
     * Get the ID of the deposit transaction.
     */
    public function getDepositId(): int;

    /**
     * Get the ID of the withdraw transaction.
     */
    public function getWithdrawId(): int;

    /**
     * Get the status of the transfer.
     */
    public function getStatus(): string;

    /**
     * Get the ID of the wallet that the transfer is from.
     */
    public function getFromId(): int|string;

    /**
     * Get the ID of the wallet that the transfer is to.
     */
    public function getToId(): int|string;

    /**
     * Get the discount amount of the transfer.
     */
    public function getDiscount(): int;

    /**
     * Get the fee amount of the transfer.
     *
     * @return non-empty-string
     */
    public function getFee(): string;

    /**
     * Get the extra information of the transfer.
     *
     * @return array<mixed>|null
     */
    public function getExtra(): ?array;

    /**
     * Get the created at timestamp of the transfer.
     */
    public function getCreatedAt(): DateTimeImmutable;

    /**
     * Get the updated at timestamp of the transfer.
     */
    public function getUpdatedAt(): DateTimeImmutable;
}
