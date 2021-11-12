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
use Bavix\Wallet\Internal\Service\PrepareService;
use Bavix\Wallet\Internal\TranslatorInterface;
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
     * @return non-empty-array<Transfer>
     */
    public function payFreeCart(CartInterface $cart): array
    {
        $basketService = app(BasketInterface::class);
        if (!$basketService->availability(new AvailabilityDto($this, $cart->getBasketDto()))) {
            throw new ProductEnded(
                app(TranslatorInterface::class)->get('wallet::errors.product_stock')
            );
        }

        app(ConsistencyInterface::class)->checkPotential($this, 0, true);

        return app(DbService::class)->transaction(function () use ($cart) {
            $transfers = [];
            $prepareService = app(PrepareService::class);
            $metaService = app(MetaService::class);
            foreach ($cart->getBasketDto()->cursor() as $product) {
                $transfers[] = $prepareService->transferLazy(
                    $this,
                    $product,
                    Transfer::STATUS_PAID,
                    0,
                    $metaService->getMeta($cart, $product)
                );
            }

            return app(CommonService::class)->applyTransfers($transfers);
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
     * @return non-empty-array<Transfer>
     */
    public function payCart(CartInterface $cart, bool $force = false): array
    {
        $basketService = app(BasketInterface::class);
        if (!$basketService->availability(new AvailabilityDto($this, $cart->getBasketDto(), $force))) {
            throw new ProductEnded(
                app(TranslatorInterface::class)->get('wallet::errors.product_stock')
            );
        }

        return app(DbService::class)->transaction(function () use ($cart, $force) {
            $transfers = [];
            $prepareService = app(PrepareService::class);
            $metaService = app(MetaService::class);
            foreach ($cart->getBasketDto()->cursor() as $product) {
                $transfers[] = $prepareService->transferLazy(
                    $this,
                    $product,
                    Transfer::STATUS_PAID,
                    $product->getAmountProduct($this),
                    $metaService->getMeta($cart, $product)
                );
            }

            if ($force === false) {
                app(ConsistencyInterface::class)->checkTransfer($transfers);
            }

            return app(CommonService::class)->applyTransfers($transfers);
        });
    }

    /**
     * @return non-empty-array<Transfer>
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

    public function refundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool
    {
        return app(DbService::class)->transaction(function () use ($cart, $force, $gifts) {
            $results = [];
            $transfers = app(PurchaseInterface::class)->already($this, $cart->getBasketDto(), $gifts);
            if (count($transfers) !== $cart->getBasketDto()->total()) {
                throw (new ModelNotFoundException())
                    ->setModel($this->transfers()->getMorphClass())
                ;
            }

            $objects = [];
            $prepareService = app(PrepareService::class);
            foreach ($cart->getBasketDto()->cursor() as $product) {
                $transfer = current($transfers);
                next($transfers);
                /**
                 * the code is extremely poorly written, a complete refactoring is required.
                 * for version 6.x we will leave it as it is.
                 */
                $transfer->load('withdraw.wallet'); // fixme: need optimize

                $objects[] = $prepareService->transferLazy(
                    $product,
                    $transfer->withdraw->wallet,
                    Transfer::STATUS_TRANSFER,
                    $transfer->deposit->amount, // fixme: need optimize
                    app(MetaService::class)->getMeta($cart, $product)
                );
            }

            if ($force === false) {
                app(ConsistencyInterface::class)->checkTransfer($objects);
            }

            app(CommonService::class)->applyTransfers($objects);

            // fixme: one query update for
            foreach ($transfers as $transfer) {
                $results[] = $transfer->update([
                    'status' => Transfer::STATUS_REFUND,
                    'status_last' => $transfer->status,
                ]);
            }

            return count(array_unique($results)) === 1;
        });
    }

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

    public function refundGiftCart(CartInterface $cart, bool $force = false): bool
    {
        return $this->refundCart($cart, $force, true);
    }

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
