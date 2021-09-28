<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use function current;

trait CanPay
{
    use CartPay;

    /**
     * @throws
     */
    public function payFree(Product $product): Transfer
    {
        return current($this->payFreeCart(app(Cart::class)->addItem($product)));
    }

    /**
     * @param bool $force
     */
    public function safePay(Product $product, bool $force = null): ?Transfer
    {
        return current($this->safePayCart(app(Cart::class)->addItem($product), $force)) ?: null;
    }

    /**
     * @param bool $force
     *
     * @throws
     */
    public function pay(Product $product, bool $force = null): Transfer
    {
        return current($this->payCart(app(Cart::class)->addItem($product), $force));
    }

    /**
     * @throws
     */
    public function forcePay(Product $product): Transfer
    {
        return current($this->forcePayCart(app(Cart::class)->addItem($product)));
    }

    /**
     * @param bool $force
     * @param bool $gifts
     */
    public function safeRefund(Product $product, bool $force = null, bool $gifts = null): bool
    {
        return $this->safeRefundCart(app(Cart::class)->addItem($product), $force, $gifts);
    }

    /**
     * @param bool $force
     * @param bool $gifts
     *
     * @throws
     */
    public function refund(Product $product, bool $force = null, bool $gifts = null): bool
    {
        return $this->refundCart(app(Cart::class)->addItem($product), $force, $gifts);
    }

    /**
     * @param bool $gifts
     *
     * @throws
     */
    public function forceRefund(Product $product, bool $gifts = null): bool
    {
        return $this->forceRefundCart(app(Cart::class)->addItem($product), $gifts);
    }

    /**
     * @param bool $force
     */
    public function safeRefundGift(Product $product, bool $force = null): bool
    {
        return $this->safeRefundGiftCart(app(Cart::class)->addItem($product), $force);
    }

    /**
     * @param bool $force
     *
     * @throws
     */
    public function refundGift(Product $product, bool $force = null): bool
    {
        return $this->refundGiftCart(app(Cart::class)->addItem($product), $force);
    }

    /**
     * @throws
     */
    public function forceRefundGift(Product $product): bool
    {
        return $this->forceRefundGiftCart(app(Cart::class)->addItem($product));
    }
}
