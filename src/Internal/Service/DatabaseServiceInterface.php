<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Illuminate\Database\RecordsNotFoundException;

interface DatabaseServiceInterface
{
    /**
     * Executes a database transaction and returns the result of the given callback.
     *
     * This method wraps a set of database operations with a transaction. If any exception occurs within the
     * transaction, the transaction will be automatically rolled back. If the callback function returns a value,
     * that value will be returned by the `transaction` method.
     *
     * @template T
     *
     * @param callable(): T $callback The callback function containing the database operations.
     * @return T The result of the callback function.
     *
     * @throws RecordsNotFoundException If the queried records are not found.
     * @throws TransactionFailedException If the transaction fails.
     * @throws ExceptionInterface If any other exception occurs.
     */
    public function transaction(callable $callback): mixed;
}
