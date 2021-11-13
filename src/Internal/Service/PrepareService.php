<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransferLazyDto;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\WalletServiceLegacy;

final class PrepareService implements PrepareServiceInterface
{
    private TransferLazyDtoAssemblerInterface $transferLazyDtoAssembler;
    private TransactionDtoAssemblerInterface $transactionDtoAssembler;
    private ConsistencyServiceInterface $consistencyService;
    private WalletServiceLegacy $walletService;
    private CastServiceInterface $castService;
    private MathServiceInterface $mathService;

    public function __construct(
        TransferLazyDtoAssemblerInterface $transferLazyDtoAssembler,
        TransactionDtoAssemblerInterface $transactionDtoAssembler,
        ConsistencyServiceInterface $consistencyService,
        WalletServiceLegacy $walletService,
        CastServiceInterface $castService,
        MathServiceInterface $mathService
    ) {
        $this->transferLazyDtoAssembler = $transferLazyDtoAssembler;
        $this->transactionDtoAssembler = $transactionDtoAssembler;
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

    /**
     * @param float|int|string $amount
     */
    public function transferLazy(Wallet $from, Wallet $to, string $status, $amount, ?array $meta = null): TransferLazyDto
    {
        $discount = $this->walletService->discount($from, $to);
        $from = $this->castService->getWallet($from);
        $fee = $this->walletService->fee($to, $amount);

        $amountWithoutDiscount = $this->mathService->sub($amount, $discount);
        $depositAmount = $this->mathService->compare($amountWithoutDiscount, 0) === -1 ? '0' : $amountWithoutDiscount;
        $withdrawAmount = $this->mathService->add($depositAmount, $fee, $from->decimal_places);

        return $this->transferLazyDtoAssembler->create(
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
