<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\ProductEnded;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\RecordsNotFoundException;

interface Customer extends Wallet
{
    public function pay(Product $product, bool $force = false): Transfer;

    public function safePay(Product $product, bool $force = false): ?Transfer;

    public function forcePay(Product $product): Transfer;

    public function paid(Product $product, bool $gifts = false): ?Transfer;

    public function refund(Product $product, bool $force = false, bool $gifts = false): bool;

    public function safeRefund(Product $product, bool $force = false, bool $gifts = false): bool;

    public function forceRefund(Product $product, bool $gifts = false): bool;

    /**
     * @throws ProductEnded
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @return Transfer[]
     */
    public function payFreeCart(CartInterface $cart): array;

    /**
     * @throws ProductEnded
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @return Transfer[]
     */
    public function payCart(CartInterface $cart, bool $force = false): array;

    /**
     * @return Transfer[]
     */
    public function safePayCart(CartInterface $cart, bool $force = false): array;

    /**
     * @return Transfer[]
     */
    public function forcePayCart(CartInterface $cart): array;

    public function refundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    public function safeRefundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    public function forceRefundCart(CartInterface $cart, bool $gifts = false): bool;
}
