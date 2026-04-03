<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Enums\TransferStatus;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\CartInterface;
use Bavix\Wallet\Internal\Assembler\AvailabilityDtoAssemblerInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\TranslatorServiceInterface;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Services\AssistantServiceInterface;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\BasketServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\EagerLoaderServiceInterface;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\PurchaseServiceInterface;
use Bavix\Wallet\Services\TransferServiceInterface;
use function count;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 *
 * @psalm-require-implements \Bavix\Wallet\Interfaces\Customer
 */
trait CartPay
{
    use HasWallet;

    /**
     * Pays basket items without charging customer balance.
     *
     * @return non-empty-array<Transfer>
     *
     * @throws ProductEnded
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function payFreeCart(CartInterface $cart): array
    {
        $atomicService = app(AtomicServiceInterface::class);
        $eagerLoaderService = app(EagerLoaderServiceInterface::class);
        $basketService = app(BasketServiceInterface::class);
        $availabilityAssembler = app(AvailabilityDtoAssemblerInterface::class);
        $translator = app(TranslatorServiceInterface::class);
        $consistencyService = app(ConsistencyServiceInterface::class);
        $castService = app(CastServiceInterface::class);
        $prepareService = app(PrepareServiceInterface::class);
        $assistantService = app(AssistantServiceInterface::class);
        $transferService = app(TransferServiceInterface::class);

        // Perform the payment for all products in the cart without any payment.
        return $atomicService->block($this, function () use (
            $cart,
            $eagerLoaderService,
            $basketService,
            $availabilityAssembler,
            $translator,
            $consistencyService,
            $castService,
            $prepareService,
            $assistantService,
            $transferService
        ) {
            // Get the basket DTO containing the products in the cart.
            $basketDto = $cart->getBasketDto();

            // Load the wallets for the products in the cart.
            $eagerLoaderService->loadWalletsByBasket($this, $basketDto);

            // Check if the products are available.
            if (! $basketService->availability($availabilityAssembler->create($this, $basketDto, false))) {
                throw new ProductEnded(
                    $translator->get('wallet::errors.product_stock'),
                    ExceptionInterface::PRODUCT_ENDED
                );
            }

            // Check if the wallet has sufficient funds.
            $consistencyService->checkPotential($this, 0, true);

            // Prepare the transfers for the products in the cart.
            $transfers = [];

            // Iterate over the items in the cart.
            foreach ($basketDto->items() as $item) {
                // Iterate over the products in the item.
                foreach ($item->getItems() as $product) {
                    // Prepare a transfer for the product.
                    $transfers[] = $prepareService->transferExtraLazy(
                        $this, // The customer who is paying for the product.
                        $castService->getWallet($this), // The customer's wallet.
                        $product, // The product to be paid.
                        $castService->getWallet(
                            $item->getReceiving() ?? $product
                        ), // The wallet to receive the payment.
                        TransferStatus::Paid, // The status of the transfer.
                        0, // The amount of the transfer.
                        $assistantService->getMeta($basketDto, $product) // The metadata of the transfer.
                    );
                }
            }

            // Ensure that at least one transfer was prepared.
            // If the above code executes without throwing an exception,
            // we can safely assume that at least one transfer was prepared.
            // This assertion is used to ensure that this assumption holds true.
            // If the assertion fails, it means that the assumption is incorrect,
            // which means that the code execution path leading to this assertion
            // is incorrect and should be investigated.
            assert($transfers !== [], 'At least one transfer must be prepared.');

            // Apply the transfers.
            return $transferService->apply($transfers);
        });
    }

    /**
     * Safe wrapper around payCart.
     */
    public function safePayCart(CartInterface $cart, bool $force = false): array
    {
        // Attempt to pay for all items in the provided cart.
        try {
            // If the payment is successful, return the array of Transfer instances.
            return $this->payCart($cart, $force);
        } catch (ExceptionInterface) {
            // If the payment fails, return an empty array.
            return [];
        }
    }

    /**
     * Pays all basket items.
     *
     * @return non-empty-array<Transfer>
     *
     * @throws ProductEnded
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function payCart(CartInterface $cart, bool $force = false): array
    {
        $atomicService = app(AtomicServiceInterface::class);
        $basketService = app(BasketServiceInterface::class);
        $availabilityAssembler = app(AvailabilityDtoAssemblerInterface::class);
        $eagerLoaderService = app(EagerLoaderServiceInterface::class);
        $castService = app(CastServiceInterface::class);
        $prepareService = app(PrepareServiceInterface::class);
        $assistantService = app(AssistantServiceInterface::class);
        $transferService = app(TransferServiceInterface::class);
        $translator = app(TranslatorServiceInterface::class);
        $consistencyService = app(ConsistencyServiceInterface::class);

        // Wrap the code in an atomic block to ensure consistency.
        return $atomicService->block($this, function () use (
            $cart,
            $force,
            $basketService,
            $availabilityAssembler,
            $eagerLoaderService,
            $castService,
            $prepareService,
            $assistantService,
            $transferService,
            $translator,
            $consistencyService
        ) {
            // Get the items in the cart.
            $basketDto = $cart->getBasketDto();

            // Load the wallets for the products in the cart.
            $eagerLoaderService->loadWalletsByBasket($this, $basketDto);

            // Check if the products are available.
            if (! $basketService->availability($availabilityAssembler->create($this, $basketDto, $force))) {
                throw new ProductEnded(
                    $translator->get('wallet::errors.product_stock'),
                    ExceptionInterface::PRODUCT_ENDED
                );
            }

            // Prepare the transfers.
            $prices = []; // Store the prices of products.
            $transfers = []; // Store the prepared transfers.
            foreach ($cart->getBasketDto()->items() as $item) {
                foreach ($item->getItems() as $product) {
                    // Get the price of the product.
                    $productId = $product::class.':'.$castService->getModel($product)->getKey();
                    $pricePerItem = $item->getPricePerItem();
                    if ($pricePerItem === null) {
                        $prices[$productId] ??= $product->getAmountProduct($this);
                        $pricePerItem = $prices[$productId];
                    }

                    // Prepare the transfer.
                    $transfers[] = $prepareService->transferExtraLazy(
                        $this,
                        $castService->getWallet($this), // The customer's wallet.
                        $product, // The product to be paid.
                        $castService->getWallet(
                            $item->getReceiving() ?? $product
                        ), // The wallet to receive the payment.
                        TransferStatus::Paid, // The status of the transfer.
                        $pricePerItem, // The amount of the transfer.
                        $assistantService->getMeta($basketDto, $product) // The metadata of the transfer.
                    );
                }
            }

            // Check that the transfers are consistent if the payment is not forced.
            if (! $force) {
                $consistencyService->checkTransfer($transfers);
            }

            // Assert that the $transfers array is not empty.
            // This is necessary to avoid a potential PHP warning
            // when calling $transferService->apply() with an empty array.
            assert($transfers !== [], 'The $transfers array must not be empty.');

            // Apply the transfers.
            return $transferService->apply($transfers);
        });
    }

    /**
     * Forces payCart execution.
     *
     * @return non-empty-array<Transfer>
     *
     * @throws ProductEnded
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forcePayCart(CartInterface $cart): array
    {
        // Call the payCart method with force set to true.
        // This method attempts to pay for all items in the provided cart.
        // If the payment is successful, an array of Transfer instances is returned.
        // If the payment fails, appropriate exceptions are thrown.
        return $this->payCart($cart, true);
    }

    /**
     * Safe wrapper around refundCart.
     */
    public function safeRefundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool
    {
        try {
            // Try to refund all items in the provided cart.
            return $this->refundCart($cart, $force, $gifts);
        } catch (ExceptionInterface) {
            // Return false if an exception occurs during the refund process.
            // This is a safe refund method, so we do not rethrow the exception.
            return false;
        }
    }

    /**
     * Refunds cart purchases.
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function refundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool
    {
        // Get the required services.
        $atomicService = app(AtomicServiceInterface::class);
        $basketDto = $cart->getBasketDto();
        $eagerLoaderService = app(EagerLoaderServiceInterface::class);
        $purchaseService = app(PurchaseServiceInterface::class);
        $castService = app(CastServiceInterface::class);
        $prepareService = app(PrepareServiceInterface::class);
        $assistantService = app(AssistantServiceInterface::class);
        $consistencyService = app(ConsistencyServiceInterface::class);
        $transferService = app(TransferServiceInterface::class);

        // Wrap the code in an atomic block to ensure consistency.
        return $atomicService->block($this, function () use (
            $force,
            $gifts,
            $basketDto,
            $eagerLoaderService,
            $purchaseService,
            $castService,
            $prepareService,
            $assistantService,
            $consistencyService,
            $transferService
        ) {
            // Load wallets by basket.
            $eagerLoaderService->loadWalletsByBasket($this, $basketDto);

            // Get already processed transfers.
            $transfers = $purchaseService->already($this, $basketDto, $gifts);

            // Check if the count of transfers is equal to the total items in the basket.
            if (count($transfers) !== $basketDto->total()) {
                throw new ModelNotFoundException(
                    sprintf('No query results for model [%s]', $this->transfers()->getModel()->getMorphClass()),
                    ExceptionInterface::MODEL_NOT_FOUND
                );
            }

            // Prepare transfers for refund.
            $index = 0;
            $objects = []; // Array to store the prepared transfers.
            $transferIds = []; // Array to store the IDs of the transfers.
            $transfers = array_values($transfers); // Convert the transfers array to indexed array.

            foreach ($basketDto->items() as $itemDto) {
                foreach ($itemDto->getItems() as $product) {
                    $transferIds[] = $transfers[$index]->getKey();
                    $objects[] = $prepareService->transferExtraLazy(
                        $product,
                        $castService->getWallet($itemDto->getReceiving() ?? $product),
                        $transfers[$index]->withdraw->wallet,
                        $transfers[$index]->withdraw->wallet,
                        TransferStatus::Transfer,
                        $transfers[$index]->deposit->amount,
                        $assistantService->getMeta($basketDto, $product)
                    );

                    $index++;
                }
            }

            // Perform consistency check if force is false.
            if (! $force) {
                $consistencyService->checkTransfer($objects);
            }

            // Ensure there are prepared transfers.
            // Assert that the array of prepared transfers is not empty.
            // If the array is empty, it means that there are no transfers to be refunded,
            // which is not expected and should not happen.
            // The assertion is added to ensure that the refund process is not executed
            // without any transfers to be refunded.
            assert($objects !== [], 'Array of prepared transfers is empty. There are no transfers to be refunded.');

            // Apply refunds.
            $transferService->apply($objects);

            // Update transfer status to refund.
            return $transferService
                ->updateStatusByIds(TransferStatus::Refund, $transferIds);
        });
    }

    /**
     * Forces refundCart execution.
     *
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function forceRefundCart(CartInterface $cart, bool $gifts = false): bool
    {
        // Set the force flag to true and perform the refund.
        // The refundCart method handles the actual refund logic.
        // By calling it with the force flag set to true, we ensure that
        // the refund is performed even if the balance is empty.
        return $this->refundCart($cart, true, $gifts);
    }

    /**
     * Safe wrapper around refundGiftCart.
     */
    public function safeRefundGiftCart(CartInterface $cart, bool $force = false): bool
    {
        try {
            // Attempt to refund all gifts in the provided cart.
            return $this->refundGiftCart($cart, $force);
        } catch (ExceptionInterface) {
            // Return false if an exception occurs during the refund process.
            // This is a safe refund method, so we do not rethrow the exception.
            return false;
        }
    }

    /**
     * Refunds gifted purchases.
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function refundGiftCart(CartInterface $cart, bool $force = false): bool
    {
        // Attempt to refund all gifts in the cart by calling the refundCart method
        // with the force flag set to the provided value and the gifts flag set to true.
        // The refundCart method handles the actual refund logic.
        return $this->refundCart($cart, $force, true);
    }

    /**
     * Forces refundGiftCart execution.
     *
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function forceRefundGiftCart(CartInterface $cart): bool
    {
        // Call the refundGiftCart method with the force flag set to true.
        // This allows the refund to be performed even if the balance is empty.
        return $this->refundGiftCart($cart, true);
    }
}
