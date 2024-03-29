<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Wallet;

/** @immutable */
final readonly class TransferLazyDto implements TransferLazyDtoInterface
{
    /**
     * @param array<mixed>|null $extra
     */
    public function __construct(
        private Wallet $fromWallet,
        private Wallet $toWallet,
        private int $discount,
        private string $fee,
        private TransactionDtoInterface $withdrawDto,
        private TransactionDtoInterface $depositDto,
        private string $status,
        private ?string $uuid,
        private ?array $extra,
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

    /**
     * @return array<mixed>|null
     */
    public function getExtra(): ?array
    {
        return $this->extra;
    }
}
