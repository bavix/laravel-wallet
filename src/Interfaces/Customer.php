<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;

interface Customer extends Wallet
{
    /**
     * @param bool $force
     *
     * @throws
     */
    public function pay(Product $product, bool $force = null): Transfer;

    /**
     * @param bool $force
     *
     * @throws
     */
    public function safePay(Product $product, bool $force = null): ?Transfer;

    /**
     * @throws
     */
    public function forcePay(Product $product): Transfer;

    /**
     * @param bool $gifts
     */
    public function paid(Product $product, bool $gifts = null): ?Transfer;

    /**
     * @param bool $force
     * @param bool $gifts
     *
     * @throws
     */
    public function refund(Product $product, bool $force = null, bool $gifts = null): bool;

    /**
     * @param bool $force
     * @param bool $gifts
     */
    public function safeRefund(Product $product, bool $force = null, bool $gifts = null): bool;

    /**
     * @param bool $gifts
     */
    public function forceRefund(Product $product, bool $gifts = null): bool;

    /**
     * @param bool $force
     *
     * @throws
     *
     * @return Transfer[]
     */
    public function payCart(Cart $cart, bool $force = null): array;

    /**
     * @param bool $force
     *
     * @throws
     *
     * @return Transfer[]
     */
    public function safePayCart(Cart $cart, bool $force = null): array;

    /**
     * @throws
     *
     * @return Transfer[]
     */
    public function forcePayCart(Cart $cart): array;

    /**
     * @param bool $force
     * @param bool $gifts
     *
     * @throws
     */
    public function refundCart(Cart $cart, bool $force = null, bool $gifts = null): bool;

    /**
     * @param bool $force
     * @param bool $gifts
     */
    public function safeRefundCart(Cart $cart, bool $force = null, bool $gifts = null): bool;

    /**
     * @param bool $gifts
     */
    public function forceRefundCart(Cart $cart, bool $gifts = null): bool;
}
