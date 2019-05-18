<?php

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

trait CartPay
{

    use HasWallet;

    /**
     * @param Cart $cart
     * @return Transfer[]
     */
    public function payFreeCart(Cart $cart): array
    {
        if (!$cart->canBuy($this)) {
            throw new ProductEnded(trans('wallet::errors.product_stock'));
        }

        $results = [];
        foreach ($cart->getItems() as $product) {
            $results[] = $this->transfer(
                $product,
                0,
                $product->getMetaProduct(),
                Transfer::STATUS_PAID
            );
        }

        return $results;
    }

    /**
     * @param Cart $cart
     * @param bool $force
     * @return Transfer[]
     */
    public function safePayCart(Cart $cart, bool $force = null): array
    {
        try {
            return $this->payCart($cart, $force);
        } catch (\Throwable $throwable) {
            return [];
        }
    }

    /**
     * @param Cart $cart
     * @param bool $force
     * @return Transfer[]
     * @throws
     */
    public function payCart(Cart $cart, bool $force = null): array
    {
        if (!$cart->canBuy($this, $force)) {
            throw new ProductEnded(trans('wallet::errors.product_stock'));
        }

        $results = [];
        foreach ($cart->getItems() as $product) {
            if ($force) {
                $results[] = $this->forceTransfer(
                    $product,
                    $product->getAmountProduct(),
                    $product->getMetaProduct(),
                    Transfer::STATUS_PAID
                );

                continue;
            }

            $results[] = $this->transfer(
                $product,
                $product->getAmountProduct(),
                $product->getMetaProduct(),
                Transfer::STATUS_PAID
            );
        }

        return $results;
    }

    /**
     * @param Cart $cart
     * @return Transfer[]
     * @throws
     */
    public function forcePayCart(Cart $cart): array
    {
        return $this->payCart($cart, true);
    }

    /**
     * @param Cart $cart
     * @param bool $force
     * @param bool $gifts
     * @return bool
     */
    public function safeRefundCart(Cart $cart, bool $force = null, bool $gifts = null): bool
    {
        try {
            return $this->refundCart($cart, $force, $gifts);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param Cart $cart
     * @param bool $force
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function refundCart(Cart $cart, bool $force = null, bool $gifts = null): bool
    {
        $results = true;
        foreach ($cart->getItems() as $product) {

            $transfer = $this->paid($product, $gifts);

            if (!$transfer) {
                throw (new ModelNotFoundException())
                    ->setModel($this->transfers()->getMorphClass());
            }

            $results = $results && DB::transaction(function () use ($product, $transfer, $force) {
                $transfer->load('withdraw.wallet');

                if ($force) {
                    $product->forceTransfer(
                        $transfer->withdraw->wallet,
                        $transfer->deposit->amount,
                        $product->getMetaProduct()
                    );
                } else {
                    $product->transfer(
                        $transfer->withdraw->wallet,
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

        return $results;
    }

    /**
     * @param Cart $cart
     * @param bool $gifts
     * @return bool
     * @throws
     */
    public function forceRefundCart(Cart $cart, bool $gifts = null): bool
    {
        return $this->refundCart($cart, true, $gifts);
    }

    /**
     * @param Cart $cart
     * @param bool $force
     * @return bool
     */
    public function safeRefundGiftCart(Cart $cart, bool $force = null): bool
    {
        try {
            return $this->refundGiftCart($cart, $force);
        } catch (\Throwable $throwable) {
            return false;
        }
    }

    /**
     * @param Cart $cart
     * @param bool $force
     * @return bool
     * @throws
     */
    public function refundGiftCart(Cart $cart, bool $force = null): bool
    {
        return $this->refundCart($cart, $force, true);
    }

    /**
     * @param Cart $cart
     * @return bool
     * @throws
     */
    public function forceRefundGiftCart(Cart $cart): bool
    {
        return $this->refundGiftCart($cart, true);
    }

    /**
     * @param Product $product
     * @param bool $gifts
     * @return null|Transfer
     */
    public function paid(Product $product, bool $gifts = null): ?Transfer
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

}
