<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\ProductInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use function current;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 *
 * @psalm-require-implements \Bavix\Wallet\Interfaces\Customer
 */
trait CanPay
{
    use CartPay;

    /**
     * Perform a free payment for a product.
     *
     * @param ProductInterface $product The product for which the payment should be made.
     * @return Transfer The Transfer instance representing the successfully paid item.
     *
     * @throws ProductEnded If the product is ended.
     * @throws BalanceIsEmpty If the balance of the wallet is empty.
     * @throws InsufficientFunds If there are insufficient funds in the wallet.
     * @throws RecordNotFoundException If the record is not found.
     * @throws RecordsNotFoundException If the records are not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     *
     * @see Bavix\Wallet\Interfaces\Customer::payFreeCart
     */
    public function payFree(ProductInterface $product): Transfer
    {
        // Create a cart with the specified product.
        $cart = app(Cart::class)->withItem($product);

        // Pay for the items in the cart without any payment.
        // Return the first transfer from the result of the payment.
        return current($this->payFreeCart($cart));
    }

    /**
     * Safely pays for a product.
     *
     * Attempts to pay for the specified product. If the payment is successful,
     * the method returns the first Transfer instance from the result of the payment.
     * If the payment fails, the method returns null.
     *
     * @param ProductInterface $product The product for which the payment should be made.
     * @param bool $force Whether to force the purchase. Defaults to false.
     * @return Transfer|null The first Transfer instance representing the successfully paid item, or null if the payment failed.
     *
     * @throws ProductEnded If the product is ended.
     * @throws BalanceIsEmpty If the balance of the wallet is empty.
     * @throws InsufficientFunds If there are insufficient funds in the wallet.
     * @throws RecordNotFoundException If the record is not found.
     * @throws RecordsNotFoundException If the records are not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     *
     * @see Bavix\Wallet\Interfaces\Customer::safePayCart
     */
    public function safePay(ProductInterface $product, bool $force = false): ?Transfer
    {
        // Create a cart with the specified product.
        $cart = app(Cart::class)->withItem($product);

        // Safely pay for the items in the cart.
        // Return the first transfer from the result of the payment, or null if the payment failed.
        return current($this->safePayCart($cart, $force)) ?: null;
    }

    /**
     * Pays for the given product.
     *
     * This method is used to pay for a specific product. It creates a cart with the given product and
     * then pays for the items in the cart. If the payment is successful, the method returns the
     * transfer object representing the payment. If the payment fails due to insufficient funds or
     * an empty balance, it throws an exception. If the payment fails due to a reason other than the
     * above, it throws a more specific exception.
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
    public function pay(ProductInterface $product, bool $force = false): Transfer
    {
        // Create a cart with the specified product.
        $cart = app(Cart::class)->withItem($product);

        // Pay for the items in the cart.
        // Return the first transfer from the result of the payment.
        return current($this->payCart($cart, $force));
    }

    /**
     * Forces the payment of the given product.
     *
     * This method forcefully attempts to pay the given product. If the payment is successful,
     * the method returns the transfer object. If the payment fails due to insufficient funds or
     * empty balance, it throws an exception. If the payment fails due to a reason
     * other than the above, it throws a more specific exception.
     *
     * @param ProductInterface $product The product for which the payment should be made.
     * @return Transfer The transfer object representing the successfully paid item.
     *
     * @throws ProductEnded If the product is ended.
     * @throws RecordNotFoundException If the record is not found.
     * @throws RecordsNotFoundException If multiple records are not found.
     * @throws TransactionFailedException If the payment transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function forcePay(ProductInterface $product): Transfer
    {
        // Create a cart with the specified product.
        $cart = app(Cart::class)->withItem($product);

        // Forcefully pay for the items in the cart.
        // Return the first transfer from the result of the payment.
        return current($this->forcePayCart($cart));
    }

    /**
     * Safely refunds the given product.
     *
     * This method attempts to refund the given product. If the refund is successful,
     * the method returns true. If the refund fails due to insufficient funds or empty balance,
     * it returns false. If the refund fails due to a reason other than the above, it throws
     * a more specific exception.
     *
     * @param ProductInterface $product The product for which the refund should be made.
     * @param bool $force Whether to force the refund even if the balance is empty.
     * @param bool $gifts Whether to refund gifts as well.
     * @return bool True if the refund is successful, false otherwise.
     *
     * @throws RecordNotFoundException If the record is not found.
     * @throws RecordsNotFoundException If multiple records are not found.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the model used in the refund is not found.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function safeRefund(ProductInterface $product, bool $force = false, bool $gifts = false): bool
    {
        // Create a cart with the specified product.
        $cart = app(Cart::class)->withItem($product);

        // Safely refund the items in the cart.
        // Return the result of the refund.
        return $this->safeRefundCart($cart, $force, $gifts);
    }

    /**
     * Refunds the given product.
     *
     * This method attempts to refund the given product. If the refund is successful,
     * the method returns true. If the refund fails due to insufficient funds or empty balance,
     * it returns false. If the refund fails due to a reason other than the above, it throws
     * a more specific exception.
     *
     * @param ProductInterface $product The product for which the refund should be made.
     * @param bool $force Whether to force the refund even if the balance is empty.
     * @param bool $gifts Whether to refund gifts as well.
     * @return bool True if the refund is successful, false otherwise.
     *
     * @throws BalanceIsEmpty If the balance of the customer is empty.
     * @throws InsufficientFunds If there are not enough funds in the wallet.
     * @throws RecordNotFoundException If the record is not found.
     * @throws RecordsNotFoundException If multiple records are not found.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the model used in the refund is not found.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function refund(ProductInterface $product, bool $force = false, bool $gifts = false): bool
    {
        // Create a cart with the specified product.
        $cart = app(Cart::class)->withItem($product);

        // Refund the items in the cart.
        // Return the result of the refund.
        return $this->refundCart($cart, $force, $gifts);
    }

    /**
     * Forcefully refunds the given product.
     *
     * This method forcefully attempts to refund the given product. If the refund is successful,
     * the method returns true. If the refund fails due to insufficient funds or empty balance,
     * it throws an exception. If the refund fails due to a reason other than the above, it throws
     * a more specific exception.
     *
     * @param ProductInterface $product The product for which the refund should be made.
     * @param bool $gifts Whether to refund gifts as well.
     * @return bool True if the refund is successful, false otherwise.
     *
     * @throws BalanceIsEmpty If the balance of the customer is empty.
     * @throws InsufficientFunds If there are not enough funds in the wallet.
     * @throws RecordNotFoundException If the record is not found.
     * @throws RecordsNotFoundException If multiple records are not found.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the model used in the refund is not found.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function forceRefund(ProductInterface $product, bool $gifts = false): bool
    {
        // Create a cart with the specified product.
        $cart = app(Cart::class)->withItem($product);

        // Forcefully refund the items in the cart.
        // Return the result of the refund.
        return $this->forceRefundCart($cart, $gifts);
    }

    /**
     * Safely refunds a gift product.
     *
     * This method safely attempts to refund the given gift product. If the refund is successful,
     * it returns true. If the refund fails due to insufficient funds or empty balance, it returns false.
     *
     * @param ProductInterface $product The gift product to be refunded.
     * @param bool $force Whether to force the refund even if the balance is empty.
     * @return bool True if the refund is successful, false otherwise.
     *
     * @throws BalanceIsEmpty If the balance of the customer is empty.
     * @throws InsufficientFunds If there are not enough funds in the wallet.
     * @throws RecordNotFoundException If the record is not found.
     * @throws RecordsNotFoundException If multiple records are not found.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the model used in the refund is not found.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function safeRefundGift(ProductInterface $product, bool $force = false): bool
    {
        // Create a cart with the specified gift product.
        $cart = app(Cart::class)->withItem($product);

        // Safely refund the items in the cart.
        // Return the result of the refund.
        return $this->safeRefundGiftCart($cart, $force);
    }

    /**
     * Refunds a gift product.
     *
     * This method attempts to refund the given gift product. If the refund is successful,
     * it returns true. If the refund fails due to insufficient funds or empty balance, it returns false.
     *
     * @param ProductInterface $product The gift product to be refunded.
     * @param bool $force Whether to force the refund even if the balance is empty.
     * @return bool True if the refund is successful, false otherwise.
     *
     * @throws BalanceIsEmpty If the balance of the customer is empty.
     * @throws InsufficientFunds If there are not enough funds in the wallet.
     * @throws RecordNotFoundException If the gift product cannot be found.
     * @throws RecordsNotFoundException If no transfers are found for the gift product.
     * @throws TransactionFailedException If the refund transaction fails.
     * @throws ModelNotFoundException If the model used in the refund is not found.
     * @throws ExceptionInterface If the refund fails for any other reason.
     */
    public function refundGift(ProductInterface $product, bool $force = false): bool
    {
        // Create a cart with the specified gift product.
        $cart = app(Cart::class)->withItem($product);

        // Attempt to refund the items in the cart.
        // Return the result of the refund.
        return $this->refundGiftCart($cart, $force);
    }

    /**
     * Forcefully refunds a gift product.
     *
     * This method is designed to handle situations where a normal refund operation might not be possible,
     * such as when a customer's wallet balance is insufficient or the item's transaction records cannot be found.
     * It forcefully attempts to refund the given gift product by creating a special cart for the refund operation
     * and then processing the refund through this cart. This approach ensures that even in cases where standard
     * refund mechanisms fail, there is a fallback option to attempt the refund, thus providing a robust solution
     * for handling refunds in exceptional circumstances.
     *
     * @param ProductInterface $product The gift product to be refunded.
     * @return bool True if the refund is successful, false otherwise.
     *
     * @throws BalanceIsEmpty Exception thrown if the customer's balance is empty, indicating
     *                         that there are no funds available for a refund.
     * @throws InsufficientFunds Exception thrown if the wallet does not have enough funds to cover the refund.
     * @throws RecordNotFoundException Exception thrown if the specified gift product cannot be located.
     * @throws RecordsNotFoundException Exception thrown if no transfer records can be found for the gift product,
     *                                  indicating that the product has not been properly processed for a refund.
     * @throws TransactionFailedException Exception thrown if the refund transaction fails to process correctly.
     * @throws ModelNotFoundException Exception thrown if the model related to the refund operation cannot be found.
     * @throws ExceptionInterface General exception thrown for any other errors encountered during the refund process.
     */
    public function forceRefundGift(ProductInterface $product): bool
    {
        // Initialize a cart specifically for the refund operation.
        // This cart is populated with the product that needs to be refunded.
        $cart = app(Cart::class)->withItem($product);

        // Attempt to refund the product by processing the refund through the initialized cart.
        // The forceRefundGiftCart method encapsulates the logic to forcefully execute the refund.
        // It returns true if the refund is successful, otherwise it will throw the appropriate exceptions
        // as defined above to indicate the nature of the failure.
        return $this->forceRefundGiftCart($cart);
    }
}
