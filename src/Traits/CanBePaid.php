<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

trait CanBePaid
{

    use HasWallet;

    /**
     * @param Product $product
     * @return Transfer
     */
    public function payFree(Product $product): Transfer
    {
        if (!$product->canBuy($this)) {
            throw new ProductEnded(trans('wallet::errors.product_stock'));
        }

        return $this->transfer($product, 0, $product->getMetaProduct());
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return Transfer
     * @throws
     */
    public function pay(Product $product, bool $force = false): Transfer
    {
        if (!$product->canBuy($this, $force)) {
            throw new ProductEnded(trans('wallet::errors.product_stock'));
        }

        if ($force) {
            return $this->forceTransfer($product, $product->getAmountProduct(), $product->getMetaProduct());
        }

        return $this->transfer($product, $product->getAmountProduct(), $product->getMetaProduct());
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return Transfer|null
     */
    public function safePay(Product $product, bool $force = false): ?Transfer
    {
        try {
            return $this->pay($product, $force);
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * @param Product $product
     * @return Transfer
     * @throws
     */
    public function forcePay(Product $product): Transfer
    {
        return $this->pay($product, true);
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
            ->first();
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return bool
     * @throws
     */
    public function refund(Product $product, bool $force = false): bool
    {
        $transfer = $this->paid($product);

        if (!$transfer) {
            throw (new ModelNotFoundException())
                ->setModel($this->transfers()->getMorphClass());
        }

        return DB::transaction(function () use ($product, $transfer, $force) {
            if ($force) {
                $product->forceTransfer($this, $transfer->deposit->amount, $product->getMetaProduct());
            } else {
                $product->transfer($this, $transfer->deposit->amount, $product->getMetaProduct());
            }

            return $transfer->update(['refund' => 1]);
        });
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return bool
     */
    public function safeRefund(Product $product, bool $force = false): bool
    {
        try {
            return $this->refund($product, $force);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param Product $product
     * @return bool
     * @throws
     */
    public function forceRefund(Product $product): bool
    {
        return $this->refund($product, true);
    }

}
