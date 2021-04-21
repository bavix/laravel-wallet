<?php

namespace Bavix\Wallet\Traits;

use function array_unique;
use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Services\MetaService;
use function count;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

trait CartPay
{
    use HasWallet;

    /**
     * @throws
     *
     * @return Transfer[]
     */
    public function payFreeCart(Cart $cart): array
    {
        if (!$cart->canBuy($this)) {
            throw new ProductEnded(trans('wallet::errors.product_stock'));
        }

        app(CommonService::class)
            ->verifyWithdraw($this, 0, true)
        ;

        $self = $this;

        return app(DbService::class)->transaction(static function () use ($self, $cart) {
            $results = [];
            foreach ($cart->getItems() as $product) {
                $results[] = app(CommonService::class)->forceTransfer(
                    $self,
                    $product,
                    0,
                    app(MetaService::class)->getMeta($cart, $product),
                    Transfer::STATUS_PAID
                );
            }

            return $results;
        });
    }

    /**
     * @return Transfer[]
     */
    public function safePayCart(Cart $cart, bool $force = false): array
    {
        try {
            return $this->payCart($cart, $force);
        } catch (Throwable $throwable) {
            return [];
        }
    }

    /**
     * @throws
     *
     * @return Transfer[]
     */
    public function payCart(Cart $cart, bool $force = false): array
    {
        if (!$cart->canBuy($this, $force)) {
            throw new ProductEnded(trans('wallet::errors.product_stock'));
        }

        $self = $this;

        return app(DbService::class)->transaction(static function () use ($self, $cart, $force) {
            $results = [];
            foreach ($cart->getItems() as $product) {
                if ($force) {
                    $results[] = app(CommonService::class)->forceTransfer(
                        $self,
                        $product,
                        $product->getAmountProduct($self),
                        app(MetaService::class)->getMeta($cart, $product),
                        Transfer::STATUS_PAID
                    );

                    continue;
                }

                $results[] = app(CommonService::class)->transfer(
                    $self,
                    $product,
                    $product->getAmountProduct($self),
                    app(MetaService::class)->getMeta($cart, $product),
                    Transfer::STATUS_PAID
                );
            }

            return $results;
        });
    }

    /**
     * @throws
     *
     * @return Transfer[]
     */
    public function forcePayCart(Cart $cart): array
    {
        return $this->payCart($cart, true);
    }

    public function safeRefundCart(Cart $cart, bool $force = false, bool $gifts = false): bool
    {
        try {
            return $this->refundCart($cart, $force, $gifts);
        } catch (Throwable $throwable) {
            return false;
        }
    }

    /**
     * @throws
     */
    public function refundCart(Cart $cart, bool $force = false, bool $gifts = false): bool
    {
        $self = $this;

        return app(DbService::class)->transaction(static function () use ($self, $cart, $force, $gifts) {
            $results = [];
            $transfers = $cart->alreadyBuy($self, $gifts);
            if (count($transfers) !== count($cart)) {
                throw (new ModelNotFoundException())
                    ->setModel($self->transfers()->getMorphClass())
                ;
            }

            foreach ($cart->getItems() as $key => $product) {
                $transfer = $transfers[$key];
                $transfer->load('withdraw.wallet');

                if (!$force) {
                    app(CommonService::class)->verifyWithdraw(
                        $product,
                        $transfer->deposit->amount
                    );
                }

                app(CommonService::class)->forceTransfer(
                    $product,
                    $transfer->withdraw->wallet,
                    $transfer->deposit->amount,
                    app(MetaService::class)->getMeta($cart, $product)
                );

                $results[] = $transfer->update([
                    'status' => Transfer::STATUS_REFUND,
                    'status_last' => $transfer->status,
                ]);
            }

            return count(array_unique($results)) === 1;
        });
    }

    /**
     * @throws
     */
    public function forceRefundCart(Cart $cart, bool $gifts = false): bool
    {
        return $this->refundCart($cart, true, $gifts);
    }

    public function safeRefundGiftCart(Cart $cart, bool $force = false): bool
    {
        try {
            return $this->refundGiftCart($cart, $force);
        } catch (Throwable $throwable) {
            return false;
        }
    }

    /**
     * @throws
     */
    public function refundGiftCart(Cart $cart, bool $force = false): bool
    {
        return $this->refundCart($cart, $force, true);
    }

    /**
     * @throws
     */
    public function forceRefundGiftCart(Cart $cart): bool
    {
        return $this->refundGiftCart($cart, true);
    }

    /**
     * Checks acquired product your wallet.
     */
    public function paid(Product $product, bool $gifts = false): ?Transfer
    {
        return current(app(Cart::class)->addItem($product)->alreadyBuy($this, $gifts)) ?: null;
    }
}
