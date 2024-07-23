<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\RecordsNotFoundException;

interface Customer extends Wallet
{
    /**
     * Purchase a product without payment.
     *
     * This method allows the user to purchase the provided product without any payment involved.
     * If the purchase is successful, the method returns the transfer object representing the purchase.
     *
     * @param ProductInterface $product The product to be purchased.
     * @return Transfer The transfer object representing the purchase.
     *
     * @throws ProductEnded If the product is ended.
     * @throws BalanceIsEmpty If the balance of the wallet is empty.
     * @throws InsufficientFunds If there are insufficient funds in the wallet.
     * @throws RecordNotFoundException If the wallet or the product record is not found.
     * @throws RecordsNotFoundException If the records are not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ModelNotFoundException If the wallet or the product is not found.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function payFree(ProductInterface $product): Transfer;

    /**
     * Attempts to purchase a product without payment.
     *
     * This method attempts to purchase the provided product without payment. If the purchase is successful,
     * the method returns the transfer object. If the purchase fails due to insufficient funds or
     * empty balance, it returns null. If the purchase fails due to a reason
     * other than the above, it throws a more specific exception.
     *
     * @param ProductInterface $product The product to be purchased.
     * @param bool $force [optional] Whether to force the purchase. Defaults to false.
     * @return Transfer|null The transfer object representing the purchase, or null if the purchase fails.
     *
     * @throws ProductEnded If the product is ended.
     * @throws BalanceIsEmpty If the balance of the wallet is empty.
     * @throws InsufficientFunds If there are insufficient funds in the wallet.
     * @throws RecordNotFoundException If the record is not found.
     * @throws RecordsNotFoundException If the records are not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function safePay(ProductInterface $product, bool $force = false): ?Transfer;

    /**
     * Pays for the given product.
     *
     * This method pays for the provided product. If the payment is successful,
     * the method returns the transfer object. If the payment fails due to insufficient funds or
     * empty balance, it throws an exception. If the payment fails due to a reason
     * other than the above, it throws a more specific exception.
     *
     * @param ProductInterface $product The product to pay for.
     * @param bool $force [optional] Whether to force the payment. Defaults to false.
     * @return Transfer The transfer object representing the payment.
     *
     * @throws ProductEnded If the product is ended.
     * @throws BalanceIsEmpty If the balance of the wallet is empty.
     * @throws InsufficientFunds If there are insufficient funds in the wallet.
     * @throws RecordNotFoundException If the record is not found.
     * @throws RecordsNotFoundException If the records are not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function pay(ProductInterface $product, bool $force = false): Transfer;

    /**
     * Forces the payment of the given product.
     *
     * This method forcefully attempts to pay the given product. If the payment is successful,
     * the method returns the transfer object. If the payment fails due to insufficient funds or
     * empty balance, it throws an exception. If the payment fails due to a reason
     * other than the above, it throws a more specific exception.
     *
     * @throws ProductEnded If the product is ended.
     * @throws RecordNotFoundException If the product cannot be found.
     * @throws RecordsNotFoundException If no transfers are found for the product.
     * @throws TransactionFailedException If the payment transaction fails.
     * @throws ExceptionInterface If the payment fails for any other reason.
     */
    public function forcePay(ProductInterface $product): Transfer;

    /**
     * Safely refunds the given product.
     *
     * This method attempts to refund the given product. If the refund is successful,
     * the method returns true. If the refund fails due to insufficient funds or
     * empty balance, it returns false. If the refund fails due to a reason
     * other than the above, it throws a more specific exception.
     *
     * @param ProductInterface $product The product to be refunded.
     * @param bool $force Whether to force the refund.
     * @param bool $gifts Whether to refund gifts.
     * @return bool Whether the refund was successful.
     *
     * @throws ProductEnded If the product is ended.
     * @throws RecordNotFoundException If the product cannot be found.
     * @throws RecordsNotFoundException If no transfers are found for the product.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ExceptionInterface If the refund fails for any other reason.
     */
    public function safeRefund(ProductInterface $product, bool $force = false, bool $gifts = false): bool;

    /**
     * Refunds the given product.
     *
     * This method attempts to refund the given product. If the refund is successful,
     * the method returns true. If the refund fails due to insufficient funds or
     * empty balance, it returns false. If the refund fails due to a reason
     * other than the above, it throws a more specific exception.
     *
     * @throws BalanceIsEmpty If the wallet's balance is empty.
     * @throws InsufficientFunds If there are not enough funds in the wallet.
     * @throws RecordNotFoundException If the product cannot be found.
     * @throws RecordsNotFoundException If no transfers are found for the product.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the refund transaction fails.
     * @throws ExceptionInterface If the refund fails for any other reason.
     */
    public function refund(ProductInterface $product, bool $force = false, bool $gifts = false): bool;

    /**
     * Force refunds a gift product.
     *
     * This method forcefully attempts to refund a gift product. If the refund is successful,
     * the method returns true. If the refund fails due to insufficient funds or
     * empty balance, it throws an exception. If the refund fails due to a reason
     * other than the above, it throws a more specific exception.
     *
     * @throws BalanceIsEmpty If the wallet's balance is empty.
     * @throws InsufficientFunds If there are not enough funds in the wallet.
     * @throws RecordNotFoundException If the gift product cannot be found.
     * @throws RecordsNotFoundException If no transfers are found for the gift product.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the refund transaction fails.
     * @throws ExceptionInterface If the refund fails for any other reason.
     */
    public function forceRefund(ProductInterface $product, bool $gifts = false): bool;

    /**
     * Safely refunds a gift product.
     *
     * This method attempts to refund a gift product. If the refund is successful,
     * the method returns true. If the refund fails due to insufficient funds or
     * empty balance, it returns false. If the refund fails due to a reason
     * other than the above, it throws a more specific exception.
     *
     * @throws BalanceIsEmpty If the wallet's balance is empty.
     * @throws InsufficientFunds If there are not enough funds in the wallet.
     * @throws RecordNotFoundException If the gift product cannot be found.
     * @throws RecordsNotFoundException If no transfers are found for the gift product.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the refund transaction fails.
     * @throws ExceptionInterface If the refund fails for any other reason.
     */
    public function safeRefundGift(ProductInterface $product, bool $force = false): bool;

    /**
     * Refunds a gift product.
     *
     * This method attempts to refund a gift product. If the refund is successful,
     * the method returns true. If the refund fails due to insufficient funds or
     * empty balance, it throws an exception. If the refund fails due to a reason
     * other than the above, it throws a more specific exception.
     *
     * @throws BalanceIsEmpty If the wallet's balance is empty.
     * @throws InsufficientFunds If there are not enough funds in the wallet.
     * @throws RecordNotFoundException If the gift product cannot be found.
     * @throws RecordsNotFoundException If no transfers are found for the gift product.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the refund transaction fails.
     * @throws ExceptionInterface If the refund fails for any other reason.
     */
    public function refundGift(ProductInterface $product, bool $force = false): bool;

    /**
     * Forcibly refunds a gift product.
     *
     * This method attempts to forcibly refund a gift product. If the refund is successful,
     * the method returns true. If the refund fails, it throws an exception.
     *
     * @return bool True if the refund is successful, false otherwise.
     *
     * @throws RecordNotFoundException If the gift product cannot be found.
     * @throws RecordsNotFoundException If no transfers are found for the gift product.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the wallet for the gift product cannot be found.
     * @throws ExceptionInterface If an unspecified exception occurs.
     */
    public function forceRefundGift(ProductInterface $product): bool;

    /**
     * Pay for all items in the given cart.
     *
     * This method pays for all items in the provided cart. If the payment is successful,
     * the method returns an array of Transfer instances representing the successfully paid items.
     * If the payment fails, an empty array is returned.
     *
     * @return non-empty-array<Transfer> An array of Transfer instances representing the successfully paid items.
     *
     * @throws ProductEnded If any of the items in the cart has expired.
     * @throws BalanceIsEmpty If the customer's balance is empty.
     * @throws InsufficientFunds If the customer's balance is not enough to cover the cost of all items in the cart.
     * @throws RecordNotFoundException If any of the items in the cart was not found.
     * @throws RecordsNotFoundException If no items were found in the cart.
     * @throws TransactionFailedException If the payment transaction failed.
     * @throws ExceptionInterface If any other exception occurred during the payment process.
     */
    public function payFreeCart(CartInterface $cart): array;

    /**
     * Safely pays for the items in the given cart.
     *
     * This method attempts to pay for all items in the provided cart. If the payment is successful,
     * the method returns an array of Transfer instances. If the payment fails, an empty array is returned.
     *
     * @return Transfer[] An array of Transfer instances representing the successfully paid items, or an empty array if the payment failed.
     */
    public function safePayCart(CartInterface $cart, bool $force = false): array;

    /**
     * Pays for the items in the given cart.
     *
     * This method pays for all items in the provided cart. If the payment is successful,
     * the method returns an array of Transfer instances. If the payment fails, the method throws an exception.
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
    public function payCart(CartInterface $cart, bool $force = false): array;

    /**
     * Forcibly pays for the items in the given cart.
     *
     * This method attempts to pay for all items in the provided cart.
     * If the payment is successful, the method returns an array of
     * Transfer instances. If the payment fails, the method throws
     * an exception.
     *
     * Please note that paying for a cart is a complex process and may
     * involve multiple transactions and database queries.
     *
     * @return non-empty-array<Transfer>
     *
     * @throws ProductEnded
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forcePayCart(CartInterface $cart): array;

    /**
     * Refunds all items in the cart and returns true if successful.
     * If refund fails, returns false.
     */
    public function safeRefundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    /**
     * Refunds all items in the cart.
     *
     * This method attempts to refund all items in the provided cart.
     * If the refund is successful, the method returns true. If the refund
     * fails, the method returns false.
     *
     * Please note that refunding a cart is a complex process and may
     * involve multiple transactions and database queries.
     *
     * @param CartInterface $cart The cart containing the items to be refunded.
     * @param bool $force Whether to force the refund even if the item is not
     *                    refundable.
     * @param bool $gifts Whether to refund gifts as well.
     *
     * @throws BalanceIsEmpty If the customer does not have enough balance.
     * @throws InsufficientFunds If the customer does not have enough balance to
     *                           refund all items in the cart.
     * @throws RecordNotFoundException If a record is not found.
     * @throws RecordsNotFoundException If records are not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ModelNotFoundException If a model is not found.
     * @throws ExceptionInterface If any other exception occurs.
     */
    public function refundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    /**
     * Refunds all items in the cart safely.
     *
     * This method attempts to refund all items in the provided cart safely.
     * If the refund is successful, the method returns true. If the refund
     * fails, the method returns false.
     *
     * @param CartInterface $cart The cart containing the items to be refunded.
     * @param bool $gifts Whether to refund gifts as well.
     * @return bool True if the refund is successful, false otherwise.
     *
     * @throws RecordNotFoundException If the cart or any of its items are not found.
     * @throws RecordsNotFoundException If the records for the cart or any of its items are not found.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the model for the cart or any of its items is not found.
     * @throws ExceptionInterface If any other exception occurs during the refund process.
     */
    public function forceRefundCart(CartInterface $cart, bool $gifts = false): bool;

    /**
     * Refunds all gifts in the cart safely.
     *
     * This method attempts to refund all gifts in the provided cart safely.
     * If the refund is successful, the method returns true. If the refund
     * fails, the method returns false.
     *
     * @param CartInterface $cart The cart containing the gifts to be refunded.
     * @param bool $force Whether to force the refund even if the balance is empty.
     */
    public function safeRefundGiftCart(CartInterface $cart, bool $force = false): bool;

    /**
     * Refunds all gifts in the cart.
     *
     * This method attempts to refund all gifts in the provided cart.
     * If the refund is successful, the method returns true. If the refund
     * fails, the method throws an exception.
     *
     * @param CartInterface $cart The cart containing the gifts to be refunded.
     * @param bool $force Whether to force the refund even if the balance is empty.
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
    public function refundGiftCart(CartInterface $cart, bool $force = false): bool;

    /**
     * Forcefully refunds all gifts in the cart.
     *
     * @param CartInterface $cart The cart to refund gifts from.
     * @return bool True if the gift refund was successful, false otherwise.
     *
     * @throws RecordNotFoundException If the cart or its items are not found.
     * @throws RecordsNotFoundException If the records for the refund are not found.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the model used in the refund is not found.
     * @throws ExceptionInterface If an unexpected error occurs.
     */
    public function forceRefundGiftCart(CartInterface $cart): bool;

    /**
     * Checks acquired product your wallet.
     *
     * Deprecated: This method is slow and will be removed in the future.
     * Instead, use the `PurchaseServiceInterface` interface.
     * With it, you can check the availability of all products with one request,
     * there will be no N-queries in the database.
     *
     * @see PurchaseServiceInterface
     */
    public function paid(ProductInterface $product, bool $gifts = false): ?Transfer;
}
