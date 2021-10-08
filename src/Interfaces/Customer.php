<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Internal\CartInterface;
use Bavix\Wallet\Models\Transfer;

interface Customer extends Wallet
{
    /**
     * @throws
     */
    public function pay(Product $product, bool $force = false): Transfer;

    /**
     * @throws
     */
    public function safePay(Product $product, bool $force = false): ?Transfer;

    /**
     * @throws
     */
    public function forcePay(Product $product): Transfer;

    public function paid(Product $product, bool $gifts = false): ?Transfer;

    /**
     * @throws
     */
    public function refund(Product $product, bool $force = false, bool $gifts = false): bool;

    public function safeRefund(Product $product, bool $force = false, bool $gifts = false): bool;

    public function forceRefund(Product $product, bool $gifts = false): bool;

    /**
     * @throws
     *
     * @return Transfer[]
     */
    public function payCart(CartInterface $cart, bool $force = false): array;

    /**
     * @throws
     *
     * @return Transfer[]
     */
    public function safePayCart(CartInterface $cart, bool $force = false): array;

    /**
     * @throws
     *
     * @return Transfer[]
     */
    public function forcePayCart(CartInterface $cart): array;

    /**
     * @throws
     */
    public function refundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    public function safeRefundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    public function forceRefundCart(CartInterface $cart, bool $gifts = false): bool;
}
