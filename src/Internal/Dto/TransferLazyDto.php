<?php

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

    public function __construct(
        Wallet $fromWallet,
        Wallet $toWallet,
        int $discount,
        string $fee,
        TransactionDto $withdrawDto,
        TransactionDto $depositDto
    ) {
        $this->fromWallet = $fromWallet;
        $this->toWallet = $toWallet;
        $this->discount = $discount;
        $this->fee = $fee;

        $this->withdrawDto = $withdrawDto;
        $this->depositDto = $depositDto;
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
}
