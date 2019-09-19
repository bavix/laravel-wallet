<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\MakeService;
use function current;

trait CanPay
{

    use CartPay;

    /**
     * @param Product $product
     * @return Transfer
     * @throws
     */
    public function payFree(Product $product): Transfer
    {
        return current($this->payFreeCart(app(MakeService::class)->makeCart()->addItem($product)));
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return Transfer|null
     */
    public function safePay(Product $product, bool $force = null): ?Transfer
    {
        return current($this->safePayCart(app(MakeService::class)->makeCart()->addItem($product), $force)) ?: null;
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return Transfer
     * @throws
     */
    public function pay(Product $product, bool $force = null): Transfer
    {
        return current($this->payCart(app(MakeService::class)->makeCart()->addItem($product), $force));
    }

    /**
     * @param Product $product
     * @return Transfer
     * @throws
     */
    public function forcePay(Product $product): Transfer
    {
        return current($this->forcePayCart(app(MakeService::class)->makeCart()->addItem($product)));
    }

    /**
     * @param Product $product
     * @param bool $force
     * @param bool $gifts
     * @return bool
     */
    public function safeRefund(Product $product, bool $force = null, bool $gifts = null): bool
    {
        return $this->safeRefundCart(app(MakeService::class)->makeCart()->addItem($product), $force, $gifts);
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
        return $this->refundCart(app(MakeService::class)->makeCart()->addItem($product), $force, $gifts);
    }

    /**
     * @param Product $product
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function forceRefund(Product $product, bool $gifts = null): bool
    {
        return $this->forceRefundCart(app(MakeService::class)->makeCart()->addItem($product), $gifts);
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return bool
     */
    public function safeRefundGift(Product $product, bool $force = null): bool
    {
        return $this->safeRefundGiftCart(app(MakeService::class)->makeCart()->addItem($product), $force);
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return bool
     * @throws
     */
    public function refundGift(Product $product, bool $force = null): bool
    {
        return $this->refundGiftCart(app(MakeService::class)->makeCart()->addItem($product), $force);
    }

    /**
     * @param Product $product
     * @return bool
     * @throws
     */
    public function forceRefundGift(Product $product): bool
    {
        return $this->forceRefundGiftCart(app(MakeService::class)->makeCart()->addItem($product));
    }

}
