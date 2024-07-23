<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Wallet;

interface TransferLazyDtoInterface
{
    /**
     * Get the wallet from which the transfer is made.
     */
    public function getFromWallet(): Wallet;

    /**
     * Get the wallet to which the transfer is made.
     */
    public function getToWallet(): Wallet;

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
     * Get the withdraw transaction DTO.
     */
    public function getWithdrawDto(): TransactionDtoInterface;

    /**
     * Get the deposit transaction DTO.
     */
    public function getDepositDto(): TransactionDtoInterface;

    /**
     * Get the status of the transfer.
     */
    public function getStatus(): string;

    /**
     * Get the UUID of the transfer.
     *
     * @return non-empty-string|null
     */
    public function getUuid(): ?string;

    /**
     * Get the extra information of the transfer.
     *
     * @return array<mixed>|null
     */
    public function getExtra(): ?array;
}
