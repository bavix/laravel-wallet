<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use function app;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssemblerInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\AtmServiceInterface;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\CommonServiceLegacy;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\DiscountServiceInterface;
use Bavix\Wallet\Services\TaxServiceInterface;
use Illuminate\Database\RecordsNotFoundException;

/**
 * Trait HasGift.
 */
trait HasGift
{
    /** Give the goods safely. */
    public function safeGift(Wallet $to, Product $product, bool $force = false): ?Transfer
    {
        try {
            return $this->gift($to, $product, $force);
        } catch (ExceptionInterface $throwable) {
            return null;
        }
    }

    /**
     * From this moment on, each user (wallet) can give
     * the goods to another user (wallet).
     * This functionality can be organized for gifts.
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function gift(Wallet $to, Product $product, bool $force = false): Transfer
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($to, $product, $force): Transfer {
            $mathService = app(MathServiceInterface::class);
            $discount = app(DiscountServiceInterface::class)->getDiscount($this, $product);
            $amount = $mathService->sub($product->getAmountProduct($this), $discount);
            $fee = app(TaxServiceInterface::class)->getFee($product, $amount);

            if ($force === false) {
                app(ConsistencyServiceInterface::class)->checkPotential($this, $mathService->add($amount, $fee));
            }

            $commonService = app(CommonServiceLegacy::class);
            $metaProduct = $product->getMetaProduct();
            $withdraw = $commonService->makeTransaction($this, Transaction::TYPE_WITHDRAW, $mathService->add($amount, $fee), $metaProduct);
            $deposit = $commonService->makeTransaction($product, Transaction::TYPE_DEPOSIT, $amount, $metaProduct);

            $castService = app(CastServiceInterface::class);

            $transfer = app(TransferDtoAssemblerInterface::class)->create(
                $deposit->getKey(),
                $withdraw->getKey(),
                Transfer::STATUS_GIFT,
                $castService->getWallet($to),
                $castService->getModel($product),
                $discount,
                $fee
            );

            $transfers = app(AtmServiceInterface::class)->makeTransfers([$transfer]);

            return current($transfers);
        });
    }

    /**
     * Santa without money gives a gift.
     *
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forceGift(Wallet $to, Product $product): Transfer
    {
        return $this->gift($to, $product, true);
    }
}
