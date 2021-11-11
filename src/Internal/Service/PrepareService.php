<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssembler;
use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransferLazyDto;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Services\ConsistencyService;
use Bavix\Wallet\Services\MathService;
use Bavix\Wallet\Services\WalletService;

class PrepareService
{
    private TransactionDtoAssembler $transactionDtoAssembler;
    private TransferDtoAssembler $transferDtoAssembler;
    private ConsistencyService $consistencyService;
    private WalletService $walletService;
    private CastService $castService;
    private MathService $mathService;

    public function __construct(
        TransactionDtoAssembler $transactionDtoAssembler,
        TransferDtoAssembler $transferDtoAssembler,
        ConsistencyService $consistencyService,
        WalletService $walletService,
        CastService $castService,
        MathService $mathService
    ) {
        $this->transactionDtoAssembler = $transactionDtoAssembler;
        $this->transferDtoAssembler = $transferDtoAssembler;
        $this->consistencyService = $consistencyService;
        $this->walletService = $walletService;
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

    public function transferLazy(Wallet $from, Wallet $to, string $status, $amount, ?array $meta = null): TransferLazyDto
    {
        $discount = $this->walletService->discount($from, $to);
        $from = $this->castService->getWallet($from);
        $fee = (string) $this->walletService->fee($to, $amount);

        // replace max => mathService.max
        $depositAmount = (string) max(0, $this->mathService->sub($amount, $discount));

        $withdrawAmount = $this->mathService->add($depositAmount, $fee, $from->decimal_places);

        return new TransferLazyDto(
            $from,
            $to,
            $discount,
            $fee,
            $this->withdraw($from, $withdrawAmount, $meta),
            $this->deposit($to, $depositAmount, $meta),
            $status
        );
    }
}
