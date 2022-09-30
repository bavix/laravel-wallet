<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;

interface StorageServiceInterface
{
    public function flush(): bool;

    public function missing(string $uuid): bool;

    /**
     * @throws RecordNotFoundException
     */
    public function get(string $uuid): string;

    public function sync(string $uuid, float|int|string $value): bool;

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function increase(string $uuid, float|int|string $value): string;

    /**
     * @param non-empty-array<string> $uuids
     *
     * @return non-empty-array<string, string>
     *
     * @throws RecordNotFoundException
     */
    public function multiGet(array $uuids): array;

    /**
     * @param non-empty-array<string, float|int|string> $inputs
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function multiSync(array $inputs): bool;

    /**
     * @param non-empty-array<string, float|int|string> $inputs
     *
     * @return non-empty-array<string, string>
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function multiIncrease(array $inputs): array;
}
