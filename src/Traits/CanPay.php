<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\ModelNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Objects\Cart;
use function current;
use Illuminate\Database\RecordsNotFoundException;

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
    public function payFree(Product $product): Transfer
    {
        return current($this->payFreeCart(app(Cart::class)->addItem($product)));
    }

    public function safePay(Product $product, bool $force = false): ?Transfer
    {
        return current($this->safePayCart(app(Cart::class)->addItem($product), $force)) ?: null;
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
    public function pay(Product $product, bool $force = false): Transfer
    {
        return current($this->payCart(app(Cart::class)->addItem($product), $force));
    }

    /**
     * @throws ProductEnded
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function forcePay(Product $product): Transfer
    {
        return current($this->forcePayCart(app(Cart::class)->addItem($product)));
    }

    public function safeRefund(Product $product, bool $force = false, bool $gifts = false): bool
    {
        return $this->safeRefundCart(app(Cart::class)->addItem($product), $force, $gifts);
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
    public function refund(Product $product, bool $force = false, bool $gifts = false): bool
    {
        return $this->refundCart(app(Cart::class)->addItem($product), $force, $gifts);
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function forceRefund(Product $product, bool $gifts = false): bool
    {
        return $this->forceRefundCart(app(Cart::class)->addItem($product), $gifts);
    }

    public function safeRefundGift(Product $product, bool $force = false): bool
    {
        return $this->safeRefundGiftCart(app(Cart::class)->addItem($product), $force);
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
    public function refundGift(Product $product, bool $force = false): bool
    {
        return $this->refundGiftCart(app(Cart::class)->addItem($product), $force);
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ModelNotFoundException
     * @throws ExceptionInterface
     */
    public function forceRefundGift(Product $product): bool
    {
        return $this->forceRefundGiftCart(app(Cart::class)->addItem($product));
    }
}
