<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\Cart;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

trait CanPay
{

    use HasWallet;

    /**
     * @param Cart $product
     * @return Transfer
     */
    public function payFree(Cart $product): Transfer
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
     * @param Cart $product
     * @param bool $force
     * @return Transfer|null
     */
    public function safePay(Cart $product, bool $force = null): ?Transfer
    {
        try {
            return $this->pay($product, $force);
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * @param Cart $product
     * @param bool $force
     * @return Transfer
     * @throws
     */
    public function pay(Cart $product, bool $force = null): Transfer
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
     * @param Cart $product
     * @return Transfer
     * @throws
     */
    public function forcePay(Cart $product): Transfer
    {
        return $this->pay($product, true);
    }

    /**
     * @param Cart $product
     * @param bool $force
     * @param bool $gifts
     * @return bool
     */
    public function safeRefund(Cart $product, bool $force = null, bool $gifts = null): bool
    {
        try {
            return $this->refund($product, $force, $gifts);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param Cart $product
     * @param bool $force
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function refund(Cart $product, bool $force = null, bool $gifts = null): bool
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
     * @param Cart $product
     * @param bool $gifts
     * @return null|Transfer
     */
    public function paid(Cart $product, bool $gifts = null): ?Transfer
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
     * @param Cart $product
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function forceRefund(Cart $product, bool $gifts = null): bool
    {
        return $this->refund($product, true, $gifts);
    }

    /**
     * @param Cart $product
     * @param bool $force
     * @return bool
     */
    public function safeRefundGift(Cart $product, bool $force = null): bool
    {
        try {
            return $this->refundGift($product, $force);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param Cart $product
     * @param bool $force
     * @return bool
     */
    public function refundGift(Cart $product, bool $force = null): bool
    {
        return $this->refund($product, $force, true);
    }

    /**
     * @param Cart $product
     * @return bool
     * @throws
     */
    public function forceRefundGift(Cart $product): bool
    {
        return $this->refundGift($product, true);
    }

}
