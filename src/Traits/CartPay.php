<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\CartInterface;
use Bavix\Wallet\Interfaces\ProductInterface;
use Bavix\Wallet\Internal\Assembler\AvailabilityDtoAssemblerInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\TranslatorServiceInterface;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
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
     * Pay for all products in the cart without any payment.
     *
     * This method performs the payment for all products in the cart without any payment.
     * It returns an array of Transfer instances representing the successfully paid items.
     *
     * @param CartInterface $cart The cart containing the products to be paid.
     * @return non-empty-array<Transfer> An array of Transfer instances representing the successfully paid items.
     *
     * @throws ProductEnded If the product is ended.
     * @throws BalanceIsEmpty If the balance of the wallet is empty.
     * @throws InsufficientFunds If there are insufficient funds in the wallet.
     * @throws RecordNotFoundException If the record is not found.
     * @throws RecordsNotFoundException If the records are not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
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
                        Transfer::STATUS_PAID, // The status of the transfer.
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
     * Safely pays for the items in the given cart.
     *
     * This method attempts to pay for all items in the provided cart. If the payment is successful,
     * the method returns an array of Transfer instances. If the payment fails, an empty array is returned.
     *
     * @param CartInterface $cart The cart containing the items to be purchased.
     * @param bool $force Whether to force the purchase. Defaults to false.
     * @return Transfer[] An array of Transfer instances representing the successfully paid items, or an empty array if the payment failed.
     */
    public function safePayCart(CartInterface $cart, bool $force = false): array
    {
        // Attempt to pay for all items in the provided cart.
        try {
            // If the payment is successful, return the array of Transfer instances.
            return $this->payCart($cart, $force);
        } catch (ExceptionInterface $exception) {
            // If the payment fails, return an empty array.
            return [];
        }
    }

    /**
     * Pays for the items in the given cart.
     *
     * This method pays for all items in the provided cart. If the payment is successful,
     * the method returns an array of Transfer instances. If the payment fails, the method throws an exception.
     *
     * @param CartInterface $cart The cart containing the items to be purchased.
     * @param bool $force Whether to force the purchase. Defaults to false.
     * @return non-empty-array<Transfer> An array of Transfer instances representing the successfully paid items.
     *
     * @throws ProductEnded If the product is ended.
     * @throws BalanceIsEmpty If the balance of the wallet is empty.
     * @throws InsufficientFunds If there are insufficient funds in the wallet.
     * @throws RecordNotFoundException If the record is not found.
     * @throws RecordsNotFoundException If the records are not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
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
                        Transfer::STATUS_PAID, // The status of the transfer.
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
     * Forcefully pays for the items in the given cart.
     *
     * This method attempts to pay for all items in the provided cart by calling the payCart method with force set to true.
     * If the payment is successful, an array of Transfer instances is returned.
     * If the payment fails, appropriate exceptions are thrown.
     *
     * @param CartInterface $cart The cart to pay for.
     * @return non-empty-array<Transfer> Array of Transfer instances if payment is successful.
     *
     * @throws ProductEnded If a product has ended.
     * @throws RecordNotFoundException If a record is not found.
     * @throws RecordsNotFoundException If multiple records are not found.
     * @throws TransactionFailedException If a transaction fails.
     * @throws ExceptionInterface If an exception occurs.
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
     * Safely attempts to refund all items in the given cart.
     *
     * This method safely attempts to refund all items in the provided cart.
     * If the refund is successful, the method returns true.
     * If the refund fails, the method returns false instead of throwing an exception.
     *
     * @param CartInterface $cart The cart to refund items from.
     * @param bool $force Whether to force the refund even if the balance is empty.
     * @param bool $gifts Whether to refund gifts as well.
     * @return bool True if the refund is successful, false otherwise.
     */
    public function safeRefundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool
    {
        try {
            // Try to refund all items in the provided cart.
            return $this->refundCart($cart, $force, $gifts);
        } catch (ExceptionInterface $e) {
            // Return false if an exception occurs during the refund process.
            // This is a safe refund method, so we do not rethrow the exception.
            return false;
        }
    }

    /**
     * Safely refunds all items in the given cart.
     *
     * This method safely attempts to refund all items in the provided cart.
     * If the refund is successful, it returns true.
     * If the refund fails, it returns false instead of throwing an exception.
     *
     * @param CartInterface $cart The cart to refund items from.
     * @param bool $force Whether to force the refund even if the balance is empty.
     * @param bool $gifts Whether to refund gifts as well.
     * @return bool True if the refund is successful, false otherwise.
     *
     * @throws BalanceIsEmpty If the balance of a wallet is empty.
     * @throws InsufficientFunds If there are insufficient funds in a wallet.
     * @throws RecordNotFoundException If a record is not found.
     * @throws RecordsNotFoundException If multiple records are not found.
     * @throws TransactionFailedException If a transaction fails.
     * @throws ModelNotFoundException If a model is not found.
     * @throws ExceptionInterface If an exception occurs.
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
                    "No query results for model [{$this->transfers()->getModel()->getMorphClass()}]",
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
                        Transfer::STATUS_TRANSFER,
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
                ->updateStatusByIds(Transfer::STATUS_REFUND, $transferIds);
        });
    }

    /**
     * Forcefully refunds all items in the cart.
     *
     * This method forcefully attempts to refund all items in the provided cart.
     * If the refund is successful, it returns true. If the refund fails due to
     * insufficient funds or empty balance, it throws an exception. If the refund
     * fails due to a reason other than the above, it throws a more specific
     * exception.
     *
     * @param CartInterface $cart The cart containing the items to be refunded.
     * @param bool $gifts Whether to refund gifts as well.
     * @return bool True if the refund is successful, false otherwise.
     *
     * @throws RecordNotFoundException If the cart or its items are not found.
     * @throws RecordsNotFoundException If the records for the refund are not found.
     * @throws TransactionFailedException If the transaction fails for any reason.
     * @throws ModelNotFoundException If the wallet or the transfer is not found.
     * @throws ExceptionInterface If the refund fails for any other reason.
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
     * Safely refunds all gifts in the given cart.
     *
     * This method attempts to refund all gifts in the provided cart safely.
     * If the refund is successful, the method returns true. If the refund
     * fails, the method returns false.
     *
     * @param CartInterface $cart The cart containing the gifts to be refunded.
     * @param bool $force Whether to force the refund even if the balance is empty.
     * @return bool True if the refund is successful, false otherwise.
     */
    public function safeRefundGiftCart(CartInterface $cart, bool $force = false): bool
    {
        try {
            // Attempt to refund all gifts in the provided cart.
            return $this->refundGiftCart($cart, $force);
        } catch (ExceptionInterface $e) {
            // Return false if an exception occurs during the refund process.
            // This is a safe refund method, so we do not rethrow the exception.
            return false;
        }
    }

    /**
     * Refunds all gifts in the given cart.
     *
     * This method attempts to refund all gifts in the provided cart.
     * If the refund is successful, the method returns true. If the refund
     * fails, the method throws an exception.
     *
     * @param CartInterface $cart The cart containing the gifts to be refunded.
     * @param bool $force Whether to force the refund even if the balance is empty.
     *                    Defaults to false.
     * @return bool True if the refund was successful, false otherwise.
     *
     * @throws BalanceIsEmpty If the balance of the customer is empty and $force is false.
     * @throws InsufficientFunds If the balance of the customer is insufficient to cover the refund.
     * @throws RecordNotFoundException If the cart or its items are not found.
     * @throws RecordsNotFoundException If the records for the refund are not found.
     * @throws TransactionFailedException If the transaction fails for any reason.
     * @throws ModelNotFoundException If the wallet or the transfer is not found.
     * @throws ExceptionInterface If any other exception occurs during the refund process.
     */
    public function refundGiftCart(CartInterface $cart, bool $force = false): bool
    {
        // Attempt to refund all gifts in the cart by calling the refundCart method
        // with the force flag set to the provided value and the gifts flag set to true.
        // The refundCart method handles the actual refund logic.
        return $this->refundCart($cart, $force, true);
    }

    /**
     * Forcefully refunds all gifts in the cart.
     *
     * This method forcefully attempts to refund all gifts in the provided cart.
     * If the refund is successful, it returns true. If the refund fails due to
     * insufficient funds or empty balance, it throws an exception. If the refund
     * fails due to a reason other than the above, it throws a more specific
     * exception.
     *
     * This method is a convenience method that calls the refundGiftCart method
     * with the force flag set to true. This allows the caller to not have to
     * worry about the force flag and just call a single method to perform the
     * refund.
     *
     * @param CartInterface $cart The cart containing the gifts to be refunded.
     * @return bool True if the gift refund was successful, false otherwise.
     *
     * @throws BalanceIsEmpty If the balance of the customer is empty and the force flag is false.
     * @throws InsufficientFunds If the balance of the customer is insufficient to cover the refund.
     * @throws RecordNotFoundException If the cart or its items are not found.
     * @throws RecordsNotFoundException If the records for the refund are not found.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the model used in the refund is not found.
     * @throws ExceptionInterface If the refund fails for any other reason.
     */
    public function forceRefundGiftCart(CartInterface $cart): bool
    {
        // Call the refundGiftCart method with the force flag set to true.
        // This allows the refund to be performed even if the balance is empty.
        return $this->refundGiftCart($cart, true);
    }

    /**
     * Checks if the given product has been acquired by the customer's wallet.
     *
     * This method is a convenience method that wraps a call to the PurchaseServiceInterface
     * to check if the given product has been acquired by the customer's wallet.
     *
     * @param ProductInterface $product The product to check.
     * @param bool $gifts Whether to include gifts in the search.
     * @return Transfer|null The associated Transfer object, or null if none exists.
     *
     * @deprecated The method is slow and will be removed in the future.
     * @see PurchaseServiceInterface
     */
    public function paid(ProductInterface $product, bool $gifts = false): ?Transfer
    {
        // Retrieve the cart with the given product.
        // The withItem method adds the given product to the cart.
        $cart = app(Cart::class)->withItem($product);

        // Use the PurchaseServiceInterface to find the associated Transfer object.
        // The PurchaseServiceInterface is responsible for finding the transfers associated
        // with a given basket.
        // The already method is used to find the transfers that are already done.
        // The basket is obtained from the cart.
        // The $gifts parameter is used to specify whether to include gifts in the search.
        $purchases = app(PurchaseServiceInterface::class)
            ->already($this, $cart->getBasketDto(), $gifts);

        // Return the first Transfer object in the array of purchases, or null if the array is empty.
        // This is a convenience method and will be removed in the future.
        return current($purchases) ?: null;
    }
}
