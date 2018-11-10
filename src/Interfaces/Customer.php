<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transfer;

interface Customer extends Wallet
{

    /**
     * @param Product $product
     * @param bool $force
     * @return Transfer
     * @throws
     */
    public function pay(Product $product, bool $force = false): Transfer;

    /**
     * @param Product $product
     * @param bool $force
     * @return null|Transfer
     * @throws
     */
    public function safePay(Product $product, bool $force = false): ?Transfer;

    /**
     * @param Product $product
     * @return Transfer
     * @throws
     */
    public function forcePay(Product $product): Transfer;

    /**
     * @param Product $product
     * @return null|Transfer
     */
    public function paid(Product $product): ?Transfer;

    /**
     * @param Product $product
     * @param bool $force
     * @return bool
     * @throws
     */
    public function refund(Product $product, bool $force = false): bool;

    /**
     * @param Product $product
     * @param bool $force
     * @return bool
     */
    public function safeRefund(Product $product, bool $force = false): bool;

    /**
     * @param Product $product
     * @return bool
     */
    public function forceRefund(Product $product): bool;

}
