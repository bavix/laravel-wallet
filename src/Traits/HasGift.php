<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Tax;
use Illuminate\Support\Facades\DB;

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
    public function safeGift(Wallet $to, Product $product, bool $force = false): ?Transfer
    {
        try {
            return $this->gift($to, $product, $force);
        } catch (\Throwable $throwable) {
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
    public function gift(Wallet $to, Product $product, bool $force = false): Transfer
    {
        /**
         * Who's giving? Let's call him Santa Claus
         */
        $santa = $this;

        /**
         * @return Transfer
         */
        $callback = function () use ($santa, $product, $force) {
            $amount = $product->getAmountProduct();
            $meta = $product->getMetaProduct();
            $fee = Tax::fee($product, $amount);

            /**
             * Santa pays taxes
             */
            if ($force) {
                $withdraw = $santa->forceWithdraw($amount + $fee, $meta);
            } else {
                $withdraw = $santa->withdraw($amount + $fee, $meta);
            }

            $deposit = $product->deposit($amount, $meta);
            return $this->assemble($product, $withdraw, $deposit, Transfer::STATUS_GIFT);
        };

        /**
         * Unfortunately,
         * I think it is wrong to make the "assemble" method public.
         * That's why I address him like this!
         */
        return DB::transaction(
            $callback->bindTo($to, \get_class($to))
        );
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
