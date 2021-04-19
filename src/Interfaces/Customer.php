<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;

interface Customer extends Wallet
{
    public function pay(Product $product, bool $force = false): Transfer;

    public function safePay(Product $product, bool $force = false): ?Transfer;

    public function forcePay(Product $product): Transfer;

    public function paid(Product $product, bool $gifts = false): ?Transfer;

    public function refund(Product $product, bool $force = false, bool $gifts = false): bool;

    public function safeRefund(Product $product, bool $force = false, bool $gifts = false): bool;

    public function forceRefund(Product $product, bool $gifts = false): bool;

    /**
     * @return Transfer[]
     */
    public function payCart(Cart $cart, bool $force = false): array;

    /**
     * @return Transfer[]
     */
    public function safePayCart(Cart $cart, bool $force = false): array;

    /**
     * @return Transfer[]
     */
    public function forcePayCart(Cart $cart): array;

    public function refundCart(Cart $cart, bool $force = false, bool $gifts = false): bool;

    public function safeRefundCart(Cart $cart, bool $force = false, bool $gifts = false): bool;

    public function forceRefundCart(Cart $cart, bool $gifts = false): bool;
}
