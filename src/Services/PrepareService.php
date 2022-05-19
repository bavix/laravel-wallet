<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\External\Contracts\CostDtoInterface;
use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\ExtraDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferLazyDtoInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;

/**
 * @internal
 */
final class PrepareService implements PrepareServiceInterface
{
    public function __construct(
        private TransferLazyDtoAssemblerInterface $transferLazyDtoAssembler,
        private TransactionDtoAssemblerInterface $transactionDtoAssembler,
        private DiscountServiceInterface $personalDiscountService,
        private ConsistencyServiceInterface $consistencyService,
        private ExtraDtoAssemblerInterface $extraDtoAssembler,
        private ExchangeServiceInterface $exchangeService,
        private CastServiceInterface $castService,
        private MathServiceInterface $mathService,
        private TaxServiceInterface $taxService
    ) {
    }

    /**
     * @throws AmountInvalid
     */
    public function deposit(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true
    ): TransactionDtoInterface {
        $this->consistencyService->checkPositive($amount);

        return $this->transactionDtoAssembler->create(
            $this->castService->getHolder($wallet),
            $this->castService->getWallet($wallet)
                ->getKey(),
            Transaction::TYPE_DEPOSIT,
            $amount,
            $confirmed,
            $meta
        );
    }

    /**
     * @throws AmountInvalid
     */
    public function withdraw(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true
    ): TransactionDtoInterface {
        $this->consistencyService->checkPositive($amount);

        return $this->transactionDtoAssembler->create(
            $this->castService->getHolder($wallet),
            $this->castService->getWallet($wallet)
                ->getKey(),
            Transaction::TYPE_WITHDRAW,
            $this->mathService->negative($amount),
            $confirmed,
            $meta
        );
    }

    /**
     * @throws AmountInvalid
     */
    public function transferLazy(
        Wallet $from,
        Wallet $to,
        string $status,
        CostDtoInterface|float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): TransferLazyDtoInterface {
        $discount = $this->personalDiscountService->getDiscount($from, $to);
        $toWallet = $this->castService->getWallet($to);
        $cost = $this->castService->getCost($amount);
        $from = $this->castService->getWallet($from);
        $fee = $this->taxService->getFee($to, $cost->getValue());

        $amountWithoutDiscount = $this->mathService->sub($cost->getValue(), $discount, $toWallet->decimal_places);
        $depositAmount = $this->mathService->compare($amountWithoutDiscount, 0) === -1 ? '0' : $amountWithoutDiscount;
        $withdrawAmount = $this->mathService->add($depositAmount, $fee, $from->decimal_places);
        $currencyRate = $cost->getCurrency() === null
            ? 1
            : $this->exchangeService->convertTo($cost->getCurrency(), $from->currency, 1);

        $withdrawCost = $this->mathService->mul($currencyRate ?? 1, $withdrawAmount);
        $withdrawCostFloor = $this->mathService->floor($withdrawCost);
        $depositCost = $this->mathService->mul($currencyRate ?? 1, $depositAmount);
        $depositCostFloor = $this->mathService->floor($depositCost);
        $extra = $this->extraDtoAssembler->create($meta);
        $withdrawOption = $extra->getWithdrawOption();
        $depositOption = $extra->getDepositOption();
        $depositMeta = $cost->getCurrency() === null
            ? $depositOption->getMeta()
            : array_merge(
                $depositOption->getMeta() ?? [],
                [
                    'cost' => $depositCostFloor,
                    'currency' => $cost->getCurrency(),
                ]
            )
        ;

        return $this->transferLazyDtoAssembler->create(
            $from,
            $toWallet,
            $discount,
            $fee,
            $this->withdraw($from, $withdrawCostFloor, $withdrawOption->getMeta(), $withdrawOption->isConfirmed()),
            $this->deposit($toWallet, $depositAmount, $depositMeta, $depositOption->isConfirmed()),
            $status
        );
    }
}
