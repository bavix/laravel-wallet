<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Decorator;

use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Internal\Service\StorageServiceInterface;

final class StorageServiceLockDecorator implements StorageServiceInterface
{
    public function __construct(
        private StorageServiceInterface $storageService,
        private LockServiceInterface $lockService
    ) {
    }

    public function flush(): bool
    {
        return $this->storageService->flush();
    }

    public function missing(string $uuid): bool
    {
        return $this->storageService->missing($uuid);
    }

    public function get(string $uuid): string
    {
        return $this->storageService->get($uuid);
    }

    public function sync(string $uuid, float|int|string $value): bool
    {
        return $this->storageService->sync($uuid, $value);
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function increase(string $uuid, float|int|string $value): string
    {
        return $this->lockService->block(
            $uuid . '::increase',
            fn () => $this->storageService->increase($uuid, $value)
        );
    }
}
