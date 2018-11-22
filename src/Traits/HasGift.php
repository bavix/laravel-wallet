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
     * From this moment on, each user (wallet) can give
     * the goods to another user (wallet).
     * This functionality can be organized for gifts.
     *
     * @param Wallet $to
     * @param Product $product
     * @return Transfer
     */
    public function gift(Wallet $to, Product $product): Transfer
    {
        /**
         * this comment is needed for syntax highlighting )
         * @var \Bavix\Wallet\Models\Wallet $to
         */
        return DB::transaction(function () use ($to, $product) {
            $amount = $product->getAmountProduct();
            $meta = $product->getMetaProduct();
            $fee = Tax::fee($product, $amount);
            $withdraw = $this->withdraw($amount + $fee, $meta);
            $deposit = $product->deposit($amount, $meta);
            return $to->assemble($product, $withdraw, $deposit);
        });
    }

    /**
     * Give the goods safely.
     *
     * @param Wallet $to
     * @param Product $product
     * @return Transfer|null
     */
    public function safeGift(Wallet $to, Product $product): ?Transfer
    {
        try {
            return $this->gift($to, $product);
        } catch (\Throwable $throwable) {
            return null;
        }
    }

}
