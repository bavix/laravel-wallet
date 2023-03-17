<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Wallet;

/** @immutable */
final class TransferLazyDto implements TransferLazyDtoInterface
{
    public function __construct(
        private readonly Wallet $fromWallet,
        private readonly Wallet $toWallet,
        private readonly int $discount,
        private readonly string $fee,
        private readonly TransactionDtoInterface $withdrawDto,
        private readonly TransactionDtoInterface $depositDto,
        private readonly string $status,
        private readonly ?string $uuid
    ) {
    }

    public function getFromWallet(): Wallet
    {
        return $this->fromWallet;
    }

    public function getToWallet(): Wallet
    {
        return $this->toWallet;
    }

    public function getDiscount(): int
    {
        return $this->discount;
    }

    public function getFee(): string
    {
        return $this->fee;
    }

    public function getWithdrawDto(): TransactionDtoInterface
    {
        return $this->withdrawDto;
    }

    public function getDepositDto(): TransactionDtoInterface
    {
        return $this->depositDto;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }
}
