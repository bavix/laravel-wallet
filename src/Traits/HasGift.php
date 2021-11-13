<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use function app;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssemblerInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\AtmServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\CommonServiceLegacy;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\LockServiceLegacy;
use Bavix\Wallet\Services\WalletServiceLegacy;
use Throwable;

/**
 * Trait HasGift.
 */
trait HasGift
{
    /**
     * Give the goods safely.
     */
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
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws Throwable
     */
    public function gift(Wallet $to, Product $product, bool $force = false): Transfer
    {
        return app(LockServiceLegacy::class)->lock($this, function () use ($to, $product, $force): Transfer {
            /**
             * Who's giving? Let's call him Santa Claus.
             *
             * @var Customer $santa
             */
            $santa = $this;

            /**
             * Unfortunately,
             * I think it is wrong to make the "assemble" method public.
             * That's why I address him like this!
             */
            return app(DatabaseServiceInterface::class)->transaction(static function () use ($santa, $to, $product, $force): Transfer {
                $math = app(MathServiceInterface::class);
                $discount = app(WalletServiceLegacy::class)->discount($santa, $product);
                $amount = $math->sub($product->getAmountProduct($santa), $discount);
                $meta = $product->getMetaProduct();
                $fee = app(WalletServiceLegacy::class)
                    ->fee($product, $amount)
                ;

                $commonService = app(CommonServiceLegacy::class);

                /**
                 * Santa pays taxes.
                 */
                if (!$force) {
                    app(ConsistencyServiceInterface::class)->checkPotential($santa, $math->add($amount, $fee));
                }

                $withdraw = $commonService->makeTransaction($santa, Transaction::TYPE_WITHDRAW, $math->add($amount, $fee), $meta);
                $deposit = $commonService->makeTransaction($product, Transaction::TYPE_DEPOSIT, $amount, $meta);

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
        });
    }

    /**
     * to give force).
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function forceGift(Wallet $to, Product $product): Transfer
    {
        return $this->gift($to, $product, true);
    }
}
