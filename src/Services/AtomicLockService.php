<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\LockInterface;
use Bavix\Wallet\Settings\LockSetting;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;

class AtomicLockService implements LockInterface
{
    private LockProvider $lockProvider;

    private string $owner;

    private int $ttl;

    public function __construct(
        UuidFactoryService $uuidFactoryService,
        CacheManagerService $cacheManagerService,
        LockSetting $lockSetting
    ) {
        $this->lockProvider = $cacheManagerService->getLockProvider(
            $lockSetting->getDriver(),
            $lockSetting->getTags(),
        );

        $this->ttl = $lockSetting->getTtl();
        $this->owner = $uuidFactoryService->uuid4();
    }

    public function acquire(string $name): bool
    {
        return (bool) $this->lock($name)->get();
    }

    public function release(string $name): bool
    {
        return $this->lock($name)->release();
    }

    private function lock(string $name): Lock
    {
        return $this->lockProvider->lock($name, $this->ttl, $this->owner);
    }
}
