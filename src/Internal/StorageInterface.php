<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal;

use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;

interface StorageInterface
{
    public function flush(): bool;

    public function missing(string $key): bool;

    /** @throws RecordNotFoundException */
    public function get(string $key): string;

    /** @param float|int|string $value */
    public function sync(string $key, $value): bool;

    /**
     * @param float|int|string $value
     *
     * @throws RecordNotFoundException
     */
    public function increase(string $key, $value): string;
}
