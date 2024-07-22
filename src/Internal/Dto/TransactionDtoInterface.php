<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use DateTimeImmutable;

interface TransactionDtoInterface
{
    /**
     * Get the UUID of the transaction.
     */
    public function getUuid(): string;

    /**
     * Get the type of the payable.
     */
    public function getPayableType(): string;

    /**
     * Get the ID of the payable.
     */
    public function getPayableId(): int|string;

    /**
     * Get the ID of the wallet.
     */
    public function getWalletId(): int;

    /**
     * Get the type of the transaction.
     */
    public function getType(): string;

    /**
     * Get the amount of the transaction.
     */
    public function getAmount(): float|int|string;

    /**
     * Check if the transaction is confirmed.
     */
    public function isConfirmed(): bool;

    /**
     * Get the meta information of the transaction.
     *
     * @return null|array<mixed>
     */
    public function getMeta(): ?array;

    /**
     * Get the created at timestamp of the transaction.
     */
    public function getCreatedAt(): DateTimeImmutable;

    /**
     * Get the updated at timestamp of the transaction.
     */
    public function getUpdatedAt(): DateTimeImmutable;
}
