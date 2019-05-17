<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transfer;

interface Customer extends Wallet
{

    /**
     * @param Cart $product
     * @param bool $force
     * @return Transfer
     * @throws
     */
    public function pay(Cart $product, bool $force = null): Transfer;

    /**
     * @param Cart $product
     * @param bool $force
     * @return null|Transfer
     * @throws
     */
    public function safePay(Cart $product, bool $force = null): ?Transfer;

    /**
     * @param Cart $product
     * @return Transfer
     * @throws
     */
    public function forcePay(Cart $product): Transfer;

    /**
     * @param Cart $product
     * @param bool $gifts
     * @return null|Transfer
     */
    public function paid(Cart $product, bool $gifts = null): ?Transfer;

    /**
     * @param Cart $product
     * @param bool $force
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function refund(Cart $product, bool $force = null, bool $gifts = null): bool;

    /**
     * @param Cart $product
     * @param bool $force
     * @param bool $gifts
     * @return bool
     */
    public function safeRefund(Cart $product, bool $force = null, bool $gifts = null): bool;

    /**
     * @param Cart $product
     * @param bool $gifts
     * @return bool
     */
    public function forceRefund(Cart $product, bool $gifts = null): bool;

}
