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

    /**
     * @return non-empty-string
     */
    public function getFee(): string
    {
        /** @var non-empty-string $fee */
        $fee = $this->fee;

        return $fee;
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

    /**
     * @return non-empty-string|null
     */
    public function getUuid(): ?string
    {
        /** @var non-empty-string|null $uuid */
        $uuid = $this->uuid;

        return $uuid;
    }

    /**
     * @return array<mixed>|null
     */
    public function getExtra(): ?array
    {
        return $this->extra;
    }
}
