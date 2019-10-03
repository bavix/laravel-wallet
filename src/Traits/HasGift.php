<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Services\LockService;
use Bavix\Wallet\Services\WalletService;
use Throwable;
use function app;

/**
 * Trait HasGift
 * @package Bavix\Wallet\Traits
 *
 * This trait should be used with the trait HasWallet.
 */
trait HasGift
{

    /**
     * Give the goods safely.
     *
     * @param Wallet $to
     * @param Product $product
     * @param bool $force
     * @return Transfer|null
     */
    public function safeGift(Wallet $to, Product $product, bool $force = null): ?Transfer
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
     * @param Wallet $to
     * @param Product $product
     * @param bool $force
     * @return Transfer
     */
    public function gift(Wallet $to, Product $product, bool $force = null): Transfer
    {
        return app(LockService::class)->lock($this, __FUNCTION__, function () use ($to, $product, $force) {
            /**
             * Who's giving? Let's call him Santa Claus
             * @var Wallet $santa
             */
            $santa = $this;

            /**
             * Unfortunately,
             * I think it is wrong to make the "assemble" method public.
             * That's why I address him like this!
             */
            return app(DbService::class)->transaction(static function () use ($santa, $to, $product, $force) {
                $discount = app(WalletService::class)->discount($santa, $product);
                $amount = $product->getAmountProduct($santa) - $discount;
                $meta = $product->getMetaProduct();
                $fee = app(WalletService::class)
                    ->fee($product, $amount);

                $commonService = app(CommonService::class);

                /**
                 * Santa pays taxes
                 */
                if (!$force) {
                    $commonService->verifyWithdraw($santa, $amount + $fee);
                }

                $withdraw = $commonService->forceWithdraw($santa, $amount + $fee, $meta);
                $deposit = $commonService->deposit($product, $amount, $meta);

                $from = app(WalletService::class)
                    ->getWallet($to);

                $transfers = $commonService->assemble([
                    app(Bring::class)
                        ->setStatus(Transfer::STATUS_GIFT)
                        ->setDiscount($discount)
                        ->setDeposit($deposit)
                        ->setWithdraw($withdraw)
                        ->setFrom($from)
                        ->setTo($product)
                ]);

                return current($transfers);
            });
        });
    }

    /**
     * to give force)
     *
     * @param Wallet $to
     * @param Product $product
     * @return Transfer
     */
    public function forceGift(Wallet $to, Product $product): Transfer
    {
        return $this->gift($to, $product, true);
    }

}
