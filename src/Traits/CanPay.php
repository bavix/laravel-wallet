<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use function current;

trait CanPay
{
    use CartPay;

    public function payFree(Product $product): Transfer
    {
        return current($this->payFreeCart(app(Cart::class)->addItem($product)));
    }

    public function safePay(Product $product, bool $force = false): ?Transfer
    {
        return current($this->safePayCart(app(Cart::class)->addItem($product), $force)) ?: null;
    }

    public function pay(Product $product, bool $force = false): Transfer
    {
        return current($this->payCart(app(Cart::class)->addItem($product), $force));
    }

    public function forcePay(Product $product): Transfer
    {
        return current($this->forcePayCart(app(Cart::class)->addItem($product)));
    }

    public function safeRefund(Product $product, bool $force = false, bool $gifts = false): bool
    {
        return $this->safeRefundCart(app(Cart::class)->addItem($product), $force, $gifts);
    }

    public function refund(Product $product, bool $force = false, bool $gifts = false): bool
    {
        return $this->refundCart(app(Cart::class)->addItem($product), $force, $gifts);
    }

    public function forceRefund(Product $product, bool $gifts = false): bool
    {
        return $this->forceRefundCart(app(Cart::class)->addItem($product), $gifts);
    }

    public function safeRefundGift(Product $product, bool $force = false): bool
    {
        return $this->safeRefundGiftCart(app(Cart::class)->addItem($product), $force);
    }

    public function refundGift(Product $product, bool $force = false): bool
    {
        return $this->refundGiftCart(app(Cart::class)->addItem($product), $force);
    }

    public function forceRefundGift(Product $product): bool
    {
        return $this->forceRefundGiftCart(app(Cart::class)->addItem($product));
    }
}
