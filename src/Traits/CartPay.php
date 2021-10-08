<?php

namespace Bavix\Wallet\Traits;

use function array_unique;
use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Internal\BasketInterface;
use Bavix\Wallet\Internal\CartInterface;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\Dto\AvailabilityDto;
use Bavix\Wallet\Internal\PurchaseInterface;
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
    public function payFreeCart(CartInterface $cart): array
    {
        $basketService = app(BasketInterface::class);
        if (!$basketService->availability(new AvailabilityDto($this, $cart->getBasketDto()))) {
            throw new ProductEnded(trans('wallet::errors.product_stock'));
        }

        app(ConsistencyInterface::class)->checkPotential($this, 0, true);

        $self = $this;

        return app(DbService::class)->transaction(static function () use ($self, $cart) {
            $results = [];
            foreach ($cart->getBasketDto()->cursor() as $product) {
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
    public function safePayCart(CartInterface $cart, bool $force = false): array
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
    public function payCart(CartInterface $cart, bool $force = false): array
    {
        $basketService = app(BasketInterface::class);
        if (!$basketService->availability(new AvailabilityDto($this, $cart->getBasketDto(), $force))) {
            throw new ProductEnded(trans('wallet::errors.product_stock'));
        }

        $self = $this;

        return app(DbService::class)->transaction(static function () use ($self, $cart, $force) {
            $results = [];
            foreach ($cart->getBasketDto()->cursor() as $product) {
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
    public function forcePayCart(CartInterface $cart): array
    {
        return $this->payCart($cart, true);
    }

    public function safeRefundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool
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
    public function refundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool
    {
        $self = $this;

        return app(DbService::class)->transaction(static function () use ($self, $cart, $force, $gifts) {
            $results = [];
            $transfers = app(PurchaseInterface::class)->already($self, $cart->getBasketDto(), $gifts);
            if (count($transfers) !== $cart->getBasketDto()->total()) {
                throw (new ModelNotFoundException())
                    ->setModel($self->transfers()->getMorphClass())
                ;
            }

            foreach ($cart->getBasketDto()->cursor() as $product) {
                $transfer = current($transfers);
                next($transfers);
                /**
                 * the code is extremely poorly written, a complete refactoring is required.
                 * for version 6.x we will leave it as it is.
                 */
                $transfer->load('withdraw.wallet');

                if (!$force) {
                    app(ConsistencyInterface::class)->checkPotential($product, $transfer->deposit->amount);
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
    public function forceRefundCart(CartInterface $cart, bool $gifts = false): bool
    {
        return $this->refundCart($cart, true, $gifts);
    }

    public function safeRefundGiftCart(CartInterface $cart, bool $force = false): bool
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
    public function refundGiftCart(CartInterface $cart, bool $force = false): bool
    {
        return $this->refundCart($cart, $force, true);
    }

    /**
     * @throws
     */
    public function forceRefundGiftCart(CartInterface $cart): bool
    {
        return $this->refundGiftCart($cart, true);
    }

    /**
     * Checks acquired product your wallet.
     */
    public function paid(Product $product, bool $gifts = false): ?Transfer
    {
        $cart = app(Cart::class)->addItem($product);
        $purchases = app(PurchaseInterface::class)
            ->already($this, $cart->getBasketDto(), $gifts)
        ;

        return current($purchases) ?: null;
    }
}
