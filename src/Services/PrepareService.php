<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\External\Contracts\ExtraDtoInterface;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\ExtraDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssemblerInterface;
use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferLazyDtoInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet as WalletModel;

/**
 * @internal
 */
final class PrepareService implements PrepareServiceInterface
{
    public function __construct(
        private readonly TransferLazyDtoAssemblerInterface $transferLazyDtoAssembler,
        private readonly TransactionDtoAssemblerInterface $transactionDtoAssembler,
        private readonly DiscountServiceInterface $personalDiscountService,
        private readonly ConsistencyServiceInterface $consistencyService,
        private readonly ExtraDtoAssemblerInterface $extraDtoAssembler,
        private readonly CastServiceInterface $castService,
        private readonly MathServiceInterface $mathService,
        private readonly TaxServiceInterface $taxService
    ) {
    }

    /**
     * @throws AmountInvalid
     */
    public function deposit(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true,
        ?string $uuid = null
    ): TransactionDtoInterface {
        $this->consistencyService->checkPositive($amount);

        return $this->transactionDtoAssembler->create(
            $this->castService->getHolder($wallet),
            $this->castService->getWallet($wallet)
                ->getKey(),
            Transaction::TYPE_DEPOSIT,
            $amount,
            $confirmed,
            $meta,
            $uuid
        );
    }

    /**
     * @throws AmountInvalid
     */
    public function withdraw(
        Wallet $wallet,
        float|int|string $amount,
        ?array $meta,
        bool $confirmed = true,
        ?string $uuid = null
    ): TransactionDtoInterface {
        $this->consistencyService->checkPositive($amount);

        return $this->transactionDtoAssembler->create(
            $this->castService->getHolder($wallet),
            $this->castService->getWallet($wallet)
                ->getKey(),
            Transaction::TYPE_WITHDRAW,
            $this->mathService->negative($amount),
            $confirmed,
            $meta,
            $uuid
        );
    }

    /**
     * @throws AmountInvalid
     */
    public function transferLazy(
        Wallet $from,
        Wallet $to,
        string $status,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): TransferLazyDtoInterface {
        return $this->transferExtraLazy(
            $from,
            $this->castService->getWallet($from),
            $to,
            $this->castService->getWallet($to),
            $status,
            $amount,
            $meta
        );
    }

    public function transferExtraLazy(
        Wallet $from,
        WalletModel $fromWallet,
        Wallet $to,
        WalletModel $toWallet,
        string $status,
        float|int|string $amount,
        ExtraDtoInterface|array|null $meta = null
    ): TransferLazyDtoInterface {
        $discount = $this->personalDiscountService->getDiscount($from, $to);
        $fee = $this->taxService->getFee($to, $amount);

        $amountWithoutDiscount = $this->mathService->sub($amount, $discount, $toWallet->decimal_places);
        $depositAmount = $this->mathService->compare($amountWithoutDiscount, 0) === -1 ? '0' : $amountWithoutDiscount;
        $withdrawAmount = $this->mathService->add($depositAmount, $fee, $fromWallet->decimal_places);
        $extra = $this->extraDtoAssembler->create($meta);
        $withdrawOption = $extra->getWithdrawOption();
        $depositOption = $extra->getDepositOption();

        $withdraw = $this->withdraw(
            $fromWallet,
            $withdrawAmount,
            $withdrawOption->getMeta(),
            $withdrawOption->isConfirmed(),
            $withdrawOption->getUuid(),
        );

        $deposit = $this->deposit(
            $toWallet,
            $depositAmount,
            $depositOption->getMeta(),
            $depositOption->isConfirmed(),
            $depositOption->getUuid(),
        );

        return $this->transferLazyDtoAssembler->create(
            $fromWallet,
            $toWallet,
            $discount,
            $fee,
            $withdraw,
            $deposit,
            $status,
            $extra->getUuid()
        );
    }
}
