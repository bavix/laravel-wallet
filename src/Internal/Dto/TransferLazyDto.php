<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Dto;

use Bavix\Wallet\Interfaces\Wallet;

class TransferLazyDto
{
    private Wallet $fromWallet;
    private Wallet $toWallet;

    private int $discount;
    private string $fee;

    private TransactionDto $depositDto;
    private TransactionDto $withdrawDto;

    private string $status;

    public function __construct(
        Wallet $fromWallet,
        Wallet $toWallet,
        int $discount,
        string $fee,
        TransactionDto $withdrawDto,
        TransactionDto $depositDto,
        string $status
    ) {
        $this->fromWallet = $fromWallet;
        $this->toWallet = $toWallet;
        $this->discount = $discount;
        $this->fee = $fee;

        $this->withdrawDto = $withdrawDto;
        $this->depositDto = $depositDto;

        $this->status = $status;
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

    public function getWithdrawDto(): TransactionDto
    {
        return $this->withdrawDto;
    }

    public function getDepositDto(): TransactionDto
    {
        return $this->depositDto;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
