<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;

interface LockServiceInterface
{
    /**
     * @template T
     * @param callable(): T $callback
     *
     * @return T
     *
     * @throws LockProviderNotFoundException
     */
    public function block(string $key, callable $callback): mixed;

    /**
     * @template T
     * @param string[] $keys
     * @param callable(): T $callback
     *
     * @return T
     *
     * @throws LockProviderNotFoundException
     */
    public function blocks(array $keys, callable $callback): mixed;

    /**
     * @param string[] $keys
     */
    public function releases(array $keys): void;

    public function isBlocked(string $key): bool;
}
