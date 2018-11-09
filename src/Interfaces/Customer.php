<?php

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\Transfer;

interface Customer extends Wallet
{

    /**
     * @param Product $product
     * @return Transfer
     * @throws
     */
    public function pay(Product $product): Transfer;

    /**
     * @param Product $product
     * @return null|Transfer
     * @throws
     */
    public function safePay(Product $product): ?Transfer;

    /**
     * @param Product $product
     * @return null|Transfer
     */
    public function paid(Product $product): ?Transfer;

    /**
     * @param Product $product
     * @return bool
     * @throws
     */
    public function refund(Product $product): bool;

    /**
     * @param Product $product
     * @return bool
     */
    public function safeRefund(Product $product): bool;

}
