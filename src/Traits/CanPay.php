<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

trait CanPay
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

        return $this->transfer(
            $product,
            0,
            $product->getMetaProduct(),
            Transfer::STATUS_PAID
        );
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
            return $this->forceTransfer(
                $product,
                $product->getAmountProduct(),
                $product->getMetaProduct(),
                Transfer::STATUS_PAID
            );
        }

        return $this->transfer(
            $product,
            $product->getAmountProduct(),
            $product->getMetaProduct(),
            Transfer::STATUS_PAID
        );
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
     * @param bool $force
     * @param bool $gifts
     * @return bool
     */
    public function safeRefund(Product $product, bool $force = false, bool $gifts = false): bool
    {
        try {
            return $this->refund($product, $force, $gifts);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param Product $product
     * @param bool $force
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function refund(Product $product, bool $force = false, bool $gifts = false): bool
    {
        $transfer = $this->paid($product, $gifts);

        if (!$transfer) {
            throw (new ModelNotFoundException())
                ->setModel($this->transfers()->getMorphClass());
        }

        return DB::transaction(function () use ($product, $transfer, $force) {
            $transfer->load('withdraw.payable');

            if ($force) {
                $product->forceTransfer(
                    $transfer->withdraw->payable,
                    $transfer->deposit->amount,
                    $product->getMetaProduct()
                );
            } else {
                $product->transfer(
                    $transfer->withdraw->payable,
                    $transfer->deposit->amount,
                    $product->getMetaProduct()
                );
            }

            return $transfer->update([
                'status' => Transfer::STATUS_REFUND,
                'status_last' => $transfer->status,
            ]);
        });
    }

    /**
     * @param Product $product
     * @param bool $gifts
     * @return null|Transfer
     */
    public function paid(Product $product, bool $gifts = false): ?Transfer
    {
        $status = [Transfer::STATUS_PAID];
        if ($gifts) {
            $status[] = Transfer::STATUS_GIFT;
        }

        /**
         * @var Model $product
         * @var Transfer $query
         */
        $query = $this->transfers();
        return $query
            ->where('to_type', $product->getMorphClass())
            ->where('to_id', $product->getKey())
            ->whereIn('status', $status)
            ->orderBy('id', 'desc')
            ->first();
    }

    /**
     * @param Product $product
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function forceRefund(Product $product, bool $gifts = false): bool
    {
        return $this->refund($product, true, $gifts);
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return bool
     */
    public function safeRefundGift(Product $product, bool $force = false): bool
    {
        try {
            return $this->refundGift($product, $force);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param Product $product
     * @param bool $force
     * @return bool
     */
    public function refundGift(Product $product, bool $force = false): bool
    {
        return $this->refund($product, $force, true);
    }

    /**
     * @param Product $product
     * @return bool
     * @throws
     */
    public function forceRefundGift(Product $product): bool
    {
        return $this->refundGift($product, true);
    }

}
