<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @api
 */
interface AtomicServiceInterface
{
    /**
     * The method atomically locks the transaction for other concurrent requests.
     *
     * The method tries to acquire a lock for the given wallet object. If the lock is acquired, it executes the
     * callback function and returns the result. If the lock is not acquired, it throws an exception.
     *
     * @template T
     *
     * @param Wallet $object The wallet object to lock the transaction.
     * @param callable(): T $callback The callback function to execute atomically.
     * @return T The result of the callback function.
     *
     * @throws RecordsNotFoundException If the records are not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     */
    public function block(Wallet $object, callable $callback): mixed;

    /**
     * This method is similar to the `block` method, but it allows you to atomically change a lot of wallets at once.
     *
     * It's useful when you need to perform multiple changes to different wallets in a single transaction.
     *
     * However, use it with caution. It generates N requests to the lock service, where N is the number of wallets.
     *
     * @template T
     *
     * @param non-empty-array<Wallet> $objects The array of wallet objects to lock the transactions.
     * @param callable(): T $callback The callback function to execute atomically.
     * @return T The result of the callback function.
     *
     * @throws RecordsNotFoundException If the records are not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If an exception occurs.
     *
     * @see AtomicServiceInterface::block
     */
    public function blocks(array $objects, callable $callback): mixed;
}
