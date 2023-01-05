<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface LockServiceInterface
{
    /**
     * @template T
     * @param callable(): T $callback
     *
     * @return T
     */
    public function block(string $key, callable $callback): mixed;

    /**
     * @template T
     * @param string[] $keys
     * @param callable(): T $callback
     *
     * @return T
     */
    public function blocks(array $keys, callable $callback): mixed;

    /**
     * @param string[] $keys
     */
    public function releases(array $keys): void;

    public function isBlocked(string $key): bool;
}
