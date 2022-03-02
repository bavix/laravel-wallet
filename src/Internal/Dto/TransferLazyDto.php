<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Wallet;

/** @psalm-immutable */
final class TransferLazyDto implements TransferLazyDtoInterface
{
    public function __construct(
        private Wallet $fromWallet,
        private Wallet $toWallet,
        private int $discount,
        private string $fee,
        private TransactionDtoInterface $withdrawDto,
        private TransactionDtoInterface $depositDto,
        private string $status
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
}
