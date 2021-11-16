<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use function array_unique;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\CartInterface;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Internal\Assembler\AvailabilityDtoAssemblerInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\TranslatorServiceInterface;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\BasketServiceInterface;
use Bavix\Wallet\Services\CommonServiceLegacy;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\MetaServiceLegacy;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\PurchaseServiceInterface;
use function count;
use Illuminate\Database\RecordsNotFoundException;

trait CartPay
{
    use HasWallet;

    /**
     * @throws ProductEnded
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @return non-empty-array<Transfer>
     */
    public function payFreeCart(CartInterface $cart): array
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($cart) {
            $basketService = app(BasketServiceInterface::class);
            $availabilityAssembler = app(AvailabilityDtoAssemblerInterface::class);
            if (!$basketService->availability($availabilityAssembler->create($this, $cart->getBasketDto(), false))) {
                throw new ProductEnded(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.product_stock'),
                    ExceptionInterface::PRODUCT_ENDED
                );
            }

            app(ConsistencyServiceInterface::class)->checkPotential($this, 0, true);

            $transfers = [];
            $prepareService = app(PrepareServiceInterface::class);
            $metaService = app(MetaServiceLegacy::class);
            foreach ($cart->getBasketDto()->cursor() as $product) {
                $transfers[] = $prepareService->transferLazy(
                    $this,
                    $product,
                    Transfer::STATUS_PAID,
                    0,
                    $metaService->getMeta($cart, $product)
                );
            }

            return app(CommonServiceLegacy::class)->applyTransfers($transfers);
        });
    }

    /** @return Transfer[] */
    public function safePayCart(CartInterface $cart, bool $force = false): array
    {
        try {
            return $this->payCart($cart, $force);
        } catch (ExceptionInterface $throwable) {
            return [];
        }
    }

    /**
     * @throws ProductEnded
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @return non-empty-array<Transfer>
     */
    public function payCart(CartInterface $cart, bool $force = false): array
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($cart, $force) {
            $basketService = app(BasketServiceInterface::class);
            $availabilityAssembler = app(AvailabilityDtoAssemblerInterface::class);
            if (!$basketService->availability($availabilityAssembler->create($this, $cart->getBasketDto(), $force))) {
                throw new ProductEnded(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.product_stock'),
                    ExceptionInterface::PRODUCT_ENDED
                );
            }

            $transfers = [];
            $prepareService = app(PrepareServiceInterface::class);
            $metaService = app(MetaServiceLegacy::class);
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
                app(ConsistencyServiceInterface::class)->checkTransfer($transfers);
            }

            return app(CommonServiceLegacy::class)->applyTransfers($transfers);
        });
    }

    /**
     * @throws ProductEnded
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
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
        } catch (ExceptionInterface $throwable) {
            return false;
        }
    }

    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function refundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool
    {
        return app(AtomicServiceInterface::class)->block($this, function () use ($cart, $force, $gifts) {
            $results = [];
            $transfers = app(PurchaseServiceInterface::class)->already($this, $cart->getBasketDto(), $gifts);
            if (count($transfers) !== $cart->getBasketDto()->total()) {
                throw new ModelNotFoundException(
                    "No query results for model [{$this->transfers()->getMorphClass()}]",
                    ExceptionInterface::MODEL_NOT_FOUND
                );
            }

            $objects = [];
            $prepareService = app(PrepareServiceInterface::class);
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
                    app(MetaServiceLegacy::class)->getMeta($cart, $product)
                );
            }

            if ($force === false) {
                app(ConsistencyServiceInterface::class)->checkTransfer($objects);
            }

            app(CommonServiceLegacy::class)->applyTransfers($objects);

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

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function forceRefundCart(CartInterface $cart, bool $gifts = false): bool
    {
        return $this->refundCart($cart, true, $gifts);
    }

    public function safeRefundGiftCart(CartInterface $cart, bool $force = false): bool
    {
        try {
            return $this->refundGiftCart($cart, $force);
        } catch (ExceptionInterface $throwable) {
            return false;
        }
    }

    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function refundGiftCart(CartInterface $cart, bool $force = false): bool
    {
        return $this->refundCart($cart, $force, true);
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
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
        $purchases = app(PurchaseServiceInterface::class)
            ->already($this, $cart->getBasketDto(), $gifts)
        ;

        return current($purchases) ?: null;
    }
}
