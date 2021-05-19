<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

interface CacheInterface
{
    /** @param null|string $default */
    public function get(string $key, $default = null);

    /** @param float|int|string $value */
    public function set(string $key, $value): bool;

    public function missing(string $key): bool;

    /** @param float|int|string $value */
    public function increment(string $key, $value);

    public function forget(string $key): bool;
}
