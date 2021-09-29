<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\StorageInterface;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Repository;

class StorageService implements StorageInterface
{
    private Repository $cache;

    public function __construct(CacheManager $cacheManager)
    {
        $this->cache = $cacheManager->driver('array');
    }

    public function flush(): bool
    {
        return $this->cache->flush();
    }

    public function missing(string $key): bool
    {
        return $this->cache->missing($key);
    }

    public function get(string $key): string
    {
        $value = $this->cache->get($key);
        if ($value === null) {
            throw new RecordNotFoundException();
        }
        return $value;
    }

    public function sync(string $key, $value): bool
    {
        return $this->cache->set($key, $value);
    }

    public function increase(string $key, $value): string
    {
        if (!$this->cache->has($key)) {
            throw new RecordNotFoundException();
        }

        return (string) $this->cache->increment($key, $value);
    }
}
