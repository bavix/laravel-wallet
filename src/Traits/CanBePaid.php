<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

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
        if (!$product->canBuy()) {
            throw new ProductEnded('The product is out of stock');
        }

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
     * @return null|Transfer
     */
    public function paid(Product $product): ?Transfer
    {
        /**
         * @var Model $product
         */
        return $this->transfers()
            ->where('to_type', $product->getMorphClass())
            ->where('to_id', $product->getKey())
            ->where('refund', 0)
            ->orderBy('id', 'desc')
            ->firstOrFail();
    }

    /**
     * @param Product $product
     * @return bool
     * @throws
     */
    public function refund(Product $product): bool
    {
        $transfer = $this->paid($product);

        if (!$transfer) {
            throw (new ModelNotFoundException())
                ->setModel($this->transfers()->getMorphClass());
        }

        return DB::transaction(function () use ($product, $transfer) {
            $product->transfer($this, $product->getAmountProduct(), $product->getMetaProduct());
            return $transfer->update(['refund' => 1]);
        });
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
