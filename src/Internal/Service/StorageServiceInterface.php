<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;

interface StorageServiceInterface
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
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function increase(string $key, $value): string;
}
