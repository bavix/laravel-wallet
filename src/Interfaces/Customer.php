<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transfer;

interface Customer extends Wallet
{
    public function payFree(Product $product): Transfer;

    public function safePay(Product $product, bool $force = false): ?Transfer;

    public function pay(Product $product, bool $force = false): Transfer;

    public function forcePay(Product $product): Transfer;

    public function safeRefund(Product $product, bool $force = false, bool $gifts = false): bool;

    public function refund(Product $product, bool $force = false, bool $gifts = false): bool;

    public function forceRefund(Product $product, bool $gifts = false): bool;

    public function safeRefundGift(Product $product, bool $force = false): bool;

    public function refundGift(Product $product, bool $force = false): bool;

    public function forceRefundGift(Product $product): bool;

    /**
     * @return non-empty-array<Transfer>
     */
    public function payFreeCart(CartInterface $cart): array;

    /**
     * @return Transfer[]
     */
    public function safePayCart(CartInterface $cart, bool $force = false): array;

    /**
     * @return non-empty-array<Transfer>
     */
    public function payCart(CartInterface $cart, bool $force = false): array;

    /**
     * @return non-empty-array<Transfer>
     */
    public function forcePayCart(CartInterface $cart): array;

    public function safeRefundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    public function refundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    public function forceRefundCart(CartInterface $cart, bool $gifts = false): bool;

    public function safeRefundGiftCart(CartInterface $cart, bool $force = false): bool;

    public function refundGiftCart(CartInterface $cart, bool $force = false): bool;

    public function forceRefundGiftCart(CartInterface $cart): bool;

    public function paid(Product $product, bool $gifts = false): ?Transfer;
}
