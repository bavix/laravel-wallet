<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferLazyDtoInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;

final class PrepareService implements PrepareServiceInterface
{
    private TransferLazyDtoAssemblerInterface $transferLazyDtoAssembler;
    private TransactionDtoAssemblerInterface $transactionDtoAssembler;
    private DiscountServiceInterface $personalDiscountService;
    private ConsistencyServiceInterface $consistencyService;
    private CastServiceInterface $castService;
    private MathServiceInterface $mathService;
    private TaxServiceInterface $taxService;

    public function __construct(
        TransferLazyDtoAssemblerInterface $transferLazyDtoAssembler,
        TransactionDtoAssemblerInterface $transactionDtoAssembler,
        DiscountServiceInterface $personalDiscountService,
        ConsistencyServiceInterface $consistencyService,
        CastServiceInterface $castService,
        MathServiceInterface $mathService,
        TaxServiceInterface $taxService
    ) {
        $this->transferLazyDtoAssembler = $transferLazyDtoAssembler;
        $this->transactionDtoAssembler = $transactionDtoAssembler;
        $this->personalDiscountService = $personalDiscountService;
        $this->consistencyService = $consistencyService;
        $this->castService = $castService;
        $this->mathService = $mathService;
        $this->taxService = $taxService;
    }

    /**
     * @throws AmountInvalid
     */
    public function deposit(Wallet $wallet, string $amount, ?array $meta, bool $confirmed = true): TransactionDtoInterface
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

    /**
     * @throws AmountInvalid
     */
    public function withdraw(Wallet $wallet, string $amount, ?array $meta, bool $confirmed = true): TransactionDtoInterface
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
     *
     * @throws AmountInvalid
     */
    public function transferLazy(Wallet $from, Wallet $to, string $status, $amount, ?array $meta = null): TransferLazyDtoInterface
    {
        $discount = $this->personalDiscountService->getDiscount($from, $to);
        $from = $this->castService->getWallet($from);
        $fee = $this->taxService->getFee($to, $amount);

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
