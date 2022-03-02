<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface StorageServiceInterface
{
    public function flush(): bool;

    public function missing(string $key): bool;

    public function get(string $key): string;

    /**
     * @param float|int|string $value
     */
    public function sync(string $key, $value): bool;

    /**
     * @param float|int|string $value
     */
    public function increase(string $key, $value): string;
}
