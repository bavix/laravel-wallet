<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

use Bavix\Wallet\Internal\CartInterface;
use Bavix\Wallet\Models\Transfer;

interface CustomerInterface extends WalletInterface
{
    public function pay(ProductInterface $product, bool $force = false): Transfer;

    public function safePay(ProductInterface $product, bool $force = false): ?Transfer;

    public function forcePay(ProductInterface $product): Transfer;

    public function paid(ProductInterface $product, bool $gifts = false): ?Transfer;

    public function refund(ProductInterface $product, bool $force = false, bool $gifts = false): bool;

    public function safeRefund(ProductInterface $product, bool $force = false, bool $gifts = false): bool;

    public function forceRefund(ProductInterface $product, bool $gifts = false): bool;

    /**
     * @return Transfer[]
     */
    public function payCart(CartInterface $cart, bool $force = false): array;

    /**
     * @return Transfer[]
     */
    public function safePayCart(CartInterface $cart, bool $force = false): array;

    /**
     * @return Transfer[]
     */
    public function forcePayCart(CartInterface $cart): array;

    public function refundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    public function safeRefundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    public function forceRefundCart(CartInterface $cart, bool $gifts = false): bool;
}
