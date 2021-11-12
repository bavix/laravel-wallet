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
use Bavix\Wallet\Internal\Assembler\TransferDtoAssembler;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\Service\AtmService;
use Bavix\Wallet\Internal\Service\CastService;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Services\LockService;
use Bavix\Wallet\Services\WalletService;
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
        } catch (Throwable $throwable) {
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
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($to, $product, $force): Transfer {
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
            return app(DbService::class)->transaction(static function () use ($santa, $to, $product, $force): Transfer {
                $math = app(MathInterface::class);
                $discount = app(WalletService::class)->discount($santa, $product);
                $amount = $math->sub($product->getAmountProduct($santa), $discount);
                $meta = $product->getMetaProduct();
                $fee = app(WalletService::class)
                    ->fee($product, $amount)
                ;

                $commonService = app(CommonService::class);

                /**
                 * Santa pays taxes.
                 */
                if (!$force) {
                    app(ConsistencyInterface::class)->checkPotential($santa, $math->add($amount, $fee));
                }

                $withdraw = $commonService->makeTransaction($santa, Transaction::TYPE_WITHDRAW, $math->add($amount, $fee), $meta);
                $deposit = $commonService->makeTransaction($product, Transaction::TYPE_DEPOSIT, $amount, $meta);

                $castService = app(CastService::class);

                $transfer = app(TransferDtoAssembler::class)->create(
                    $deposit->getKey(),
                    $withdraw->getKey(),
                    Transfer::STATUS_GIFT,
                    $castService->getWallet($to),
                    $castService->getModel($product),
                    $discount,
                    $fee
                );

                $transfers = app(AtmService::class)->makeTransfers([$transfer]);

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
