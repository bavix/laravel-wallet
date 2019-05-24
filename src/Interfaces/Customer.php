<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;

interface Customer extends Wallet
{

    /**
     * @param Product $product
     * @param bool $force
     * @return Transfer
     * @throws
     */
    public function pay(Product $product, bool $force = null): Transfer;

    /**
     * @param Product $product
     * @param bool $force
     * @return null|Transfer
     * @throws
     */
    public function safePay(Product $product, bool $force = null): ?Transfer;

    /**
     * @param Product $product
     * @return Transfer
     * @throws
     */
    public function forcePay(Product $product): Transfer;

    /**
     * @param Product $product
     * @param bool $gifts
     * @return null|Transfer
     */
    public function paid(Product $product, bool $gifts = null): ?Transfer;

    /**
     * @param Product $product
     * @param bool $force
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function refund(Product $product, bool $force = null, bool $gifts = null): bool;

    /**
     * @param Product $product
     * @param bool $force
     * @param bool $gifts
     * @return bool
     */
    public function safeRefund(Product $product, bool $force = null, bool $gifts = null): bool;

    /**
     * @param Product $product
     * @param bool $gifts
     * @return bool
     */
    public function forceRefund(Product $product, bool $gifts = null): bool;

    /**
     * @param Cart $cart
     * @param bool $force
     * @return Transfer[]
     * @throws
     */
    public function payCart(Cart $cart, bool $force = null): array;

    /**
     * @param Cart $cart
     * @param bool $force
     * @return Transfer[]
     * @throws
     */
    public function safePayCart(Cart $cart, bool $force = null): array;

    /**
     * @param Cart $cart
     * @return Transfer[]
     * @throws
     */
    public function forcePayCart(Cart $cart): array;

    /**
     * @param Cart $cart
     * @param bool $force
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function refundCart(Cart $cart, bool $force = null, bool $gifts = null): bool;

    /**
     * @param Cart $cart
     * @param bool $force
     * @param bool $gifts
     * @return bool
     */
    public function safeRefundCart(Cart $cart, bool $force = null, bool $gifts = null): bool;

    /**
     * @param Cart $cart
     * @param bool $gifts
     * @return bool
     */
    public function forceRefundCart(Cart $cart, bool $gifts = null): bool;
}
