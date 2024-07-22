<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface LockServiceInterface
{
    /**
     * Locks the transaction for other concurrent requests.
     *
     * @template T
     *
     * @param string $key The key to lock.
     * @param callable(): T $callback The callback to execute after the lock has been acquired.
     * @return T The result of the callback.
     */
    public function block(string $key, callable $callback): mixed;

    /**
     * Locks multiple transactions for other concurrent requests.
     *
     * This method attempts to acquire the locks for all the given keys. If all the locks are acquired,
     * it executes the callback and returns the result. If any of the locks cannot be acquired,
     * it releases any acquired locks and returns the result of the callback immediately.
     *
     * @template T
     *
     * @param string[] $keys The keys to lock.
     * @param callable(): T $callback The callback to execute after the locks have been acquired.
     * @return T The result of the callback.
     */
    public function blocks(array $keys, callable $callback): mixed;

    /**
     * Releases the locks for the given keys.
     *
     * @param string[] $keys The keys to release the locks for.
     */
    public function releases(array $keys): void;

    /**
     * Check if the given key is locked.
     *
     * @param string $key The key to check.
     * @return bool Whether the key is locked or not.
     */
    public function isBlocked(string $key): bool;
}
