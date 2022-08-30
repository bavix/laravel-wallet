<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\AsyncTransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;

final class AsyncPrepareService implements AsyncPrepareServiceInterface
{
    public function __construct(
        private AsyncTransactionDtoAssemblerInterface $asyncTransactionDtoAssembler,
        private ConsistencyServiceInterface $consistencyService,
        private CastServiceInterface $castService,
        private MathServiceInterface $mathService
    ) {
    }

    public function deposit(
        string $uuid,
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true
    ): TransactionDtoInterface {
        $this->consistencyService->checkPositive($amount);

        return $this->asyncTransactionDtoAssembler->create(
            $uuid,
            $this->castService->getHolder($wallet),
            $this->castService->getWallet($wallet)
                ->getKey(),
            Transaction::TYPE_DEPOSIT,
            $amount,
            $confirmed,
            $meta
        );
    }

    public function withdraw(
        string $uuid,
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true
    ): TransactionDtoInterface {
        $this->consistencyService->checkPositive($amount);

        return $this->asyncTransactionDtoAssembler->create(
            $uuid,
            $this->castService->getHolder($wallet),
            $this->castService->getWallet($wallet)
                ->getKey(),
            Transaction::TYPE_WITHDRAW,
            $this->mathService->negative($amount),
            $confirmed,
            $meta
        );
    }
}
