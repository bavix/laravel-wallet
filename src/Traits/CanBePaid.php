<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Model;

trait CanBePaid
{

    use HasWallet;

    /**
     * @param Product $product
     * @return Transfer
     * @throws 
     */
    public function pay(Product $product): Transfer
    {
        return $this->transfer($product, $product->getAmountProduct(), $product->getMetaProduct());
    }

    /**
     * @param Product $product
     * @return Transfer|null
     */
    public function safePay(Product $product): ?Transfer
    {
        try {
            return $this->pay($product);
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * @param Product $product
     * @return bool
     * @throws
     */
    public function refund(Product $product): bool
    {
        /**
         * @var Model $product
         */
        $this->transfers()
            ->where('to_type', $product->getMorphClass())
            ->where('to_id', $product->getKey())
            ->orderBy('id', 'desc')
            ->firstOrFail();

        return $product
            ->transfer($this, $product->getAmountProduct(), $product->getMetaProduct())
            ->exists;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function safeRefund(Product $product): bool
    {
        try {
            return $this->refund($product);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

}
