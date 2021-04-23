<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Illuminate\Cache\CacheManager;
use Illuminate\Cache\TaggedCache;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use RuntimeException;

class CacheManagerService
{
    private CacheManager $cacheManager;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public function getRepository(?string $driver, array $tags): Repository
    {
        $repository = $this->cacheManager->driver($driver);
        if ($repository instanceof TaggedCache && count($tags) > 0 && $repository->supportsTags()) {
            return $repository->tags($tags);
        }

        return $repository;
    }

    public function getStore(?string $driver, array $tags): Store
    {
        return $this->getRepository($driver, $tags)->getStore();
    }

    public function getLockProvider(?string $driver, array $tags): LockProvider
    {
        $store = $this->getStore($driver, $tags);
        if ($store instanceof LockProvider) {
            return $store;
        }

        throw new RuntimeException('The repository does not support locks: '.get_class($store));
    }
}
