<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Illuminate\Database\RecordsNotFoundException;

interface AtomicServiceInterface
{
    /**
     * The method atomically locks the transaction for other concurrent requests.
     *
     * @template T
     * @param callable(): T $callback
     * @return T
     *
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function block(Wallet $object, callable $callback): mixed;

    /**
     * Use when you need to atomically change a lot of wallets and atomic in its pure form is not suitable. Use with
     * caution, generates N requests to the lock service.
     *
     * @template T
     * @param non-empty-array<Wallet> $objects
     * @param callable(): T $callback
     * @return T
     *
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function blocks(array $objects, callable $callback): mixed;
}
