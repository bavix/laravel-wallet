<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\ProductInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use function current;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @psalm-require-extends \Illuminate\Database\Eloquent\Model
 * @psalm-require-implements \Bavix\Wallet\Interfaces\Customer
 */
trait CanPay
{
    use CartPay;

    /**
     * @throws ProductEnded
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function payFree(ProductInterface $product): Transfer
    {
        return current($this->payFreeCart(app(Cart::class)->withItem($product)));
    }

    public function safePay(ProductInterface $product, bool $force = false): ?Transfer
    {
        return current($this->safePayCart(app(Cart::class)->withItem($product), $force)) ?: null;
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
     */
    public function pay(ProductInterface $product, bool $force = false): Transfer
    {
        return current($this->payCart(app(Cart::class)->withItem($product), $force));
    }

    /**
     * @throws ProductEnded
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forcePay(ProductInterface $product): Transfer
    {
        return current($this->forcePayCart(app(Cart::class)->withItem($product)));
    }

    public function safeRefund(ProductInterface $product, bool $force = false, bool $gifts = false): bool
    {
        return $this->safeRefundCart(app(Cart::class)->withItem($product), $force, $gifts);
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
    public function refund(ProductInterface $product, bool $force = false, bool $gifts = false): bool
    {
        return $this->refundCart(app(Cart::class)->withItem($product), $force, $gifts);
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function forceRefund(ProductInterface $product, bool $gifts = false): bool
    {
        return $this->forceRefundCart(app(Cart::class)->withItem($product), $gifts);
    }

    public function safeRefundGift(ProductInterface $product, bool $force = false): bool
    {
        return $this->safeRefundGiftCart(app(Cart::class)->withItem($product), $force);
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
    public function refundGift(ProductInterface $product, bool $force = false): bool
    {
        return $this->refundGiftCart(app(Cart::class)->withItem($product), $force);
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function forceRefundGift(ProductInterface $product): bool
    {
        return $this->forceRefundGiftCart(app(Cart::class)->withItem($product));
    }
}
