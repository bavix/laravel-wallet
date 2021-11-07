<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssembler;
use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Services\ConsistencyService;
use Bavix\Wallet\Services\MathService;

class PrepareService
{
    private TransactionDtoAssembler $transactionDtoAssembler;
    private TransferDtoAssembler $transferDtoAssembler;
    private ConsistencyService $consistencyService;
    private CastService $castService;
    private MathService $mathService;

    public function __construct(
        TransactionDtoAssembler $transactionDtoAssembler,
        TransferDtoAssembler $transferDtoAssembler,
        ConsistencyService $consistencyService,
        CastService $castService,
        MathService $mathService
    ) {
        $this->transactionDtoAssembler = $transactionDtoAssembler;
        $this->transferDtoAssembler = $transferDtoAssembler;
        $this->consistencyService = $consistencyService;
        $this->castService = $castService;
        $this->mathService = $mathService;
    }

    public function deposit(Wallet $wallet, string $amount, ?array $meta, bool $confirmed = true): TransactionDto
    {
        $this->consistencyService->checkPositive($amount);

        return $this->transactionDtoAssembler->create(
            $this->castService->getHolder($wallet),
            $this->castService->getWallet($wallet)->getKey(),
            Transaction::TYPE_DEPOSIT,
            $amount,
            $confirmed,
            $meta
        );
    }

    public function withdraw(Wallet $wallet, string $amount, ?array $meta, bool $confirmed = true): TransactionDto
    {
        $this->consistencyService->checkPositive($amount);

        return $this->transactionDtoAssembler->create(
            $this->castService->getHolder($wallet),
            $this->castService->getWallet($wallet)->getKey(),
            Transaction::TYPE_WITHDRAW,
            $this->mathService->negative($amount),
            $confirmed,
            $meta
        );
    }
}
