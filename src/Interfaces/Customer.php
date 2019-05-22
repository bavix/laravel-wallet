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
}
