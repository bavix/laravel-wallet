<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

trait CanPay
{

    use HasWallet;
    use CartPay;

    /**
     * @param Product $product
     * @return Transfer
     */
    public function payFree(Product $product): Transfer
    {
        return \current($this->payFreeCart(Cart::make()->addItem($product)));
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return Transfer|null
     */
    public function safePay(Product $product, bool $force = null): ?Transfer
    {
        try {
            return $this->pay($product, $force);
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return Transfer
     * @throws
     */
    public function pay(Product $product, bool $force = null): Transfer
    {
        return \current($this->payCart(Cart::make()->addItem($product), $force));
    }

    /**
     * @param Product $product
     * @return Transfer
     * @throws
     */
    public function forcePay(Product $product): Transfer
    {
        return \current($this->forcePayCart(Cart::make()->addItem($product)));
    }

    /**
     * @param Product $product
     * @param bool $force
     * @param bool $gifts
     * @return bool
     */
    public function safeRefund(Product $product, bool $force = null, bool $gifts = null): bool
    {
        return $this->safeRefundCart(Cart::make()->addItem($product), $force, $gifts);
    }

    /**
     * @param Product $product
     * @param bool $force
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function refund(Product $product, bool $force = null, bool $gifts = null): bool
    {
        return $this->refundCart(Cart::make()->addItem($product), $force, $gifts);
    }

    /**
     * @param Product $product
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function forceRefund(Product $product, bool $gifts = null): bool
    {
        return $this->forceRefundCart(Cart::make()->addItem($product), $gifts);
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return bool
     */
    public function safeRefundGift(Product $product, bool $force = null): bool
    {
        return $this->safeRefundGiftCart(Cart::make()->addItem($product), $force);
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return bool
     * @throws
     */
    public function refundGift(Product $product, bool $force = null): bool
    {
        return $this->refundGiftCart(Cart::make()->addItem($product), $force);
    }

    /**
     * @param Product $product
     * @return bool
     * @throws
     */
    public function forceRefundGift(Product $product): bool
    {
        return $this->forceRefundGiftCart(Cart::make()->addItem($product));
    }

}
