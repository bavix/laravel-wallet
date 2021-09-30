<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Bavix\Wallet\Internal\LockInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\StorageInterface;
use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class StorageService implements StorageInterface
{
    private CacheRepository $cache;

    private LockInterface $lock;

    private MathInterface $math;

    public function __construct(
        CacheManager $cacheManager,
        ConfigRepository $config,
        LockInterface $lock,
        MathInterface $math
    ) {
        $this->math = $math;
        $this->lock = $lock;
        $this->cache = $cacheManager->driver(
            $config->get('wallet.cache.driver', 'array')
        );
    }

    public function flush(): bool
    {
        return $this->cache->flush();
    }

    public function missing(string $key): bool
    {
        return $this->cache->delete($key);
    }

    public function get(string $key): string
    {
        $value = $this->cache->get($key);
        if ($value === null) {
            throw new RecordNotFoundException();
        }

        return $this->math->round($value);
    }

    public function sync(string $key, $value): bool
    {
        return $this->cache->set($key, $value);
    }

    public function increase(string $key, $value): string
    {
        return $this->lock->block(
            $key,
            function () use ($key, $value): string {
                $result = $this->math->add($this->get($key), $value);
                $this->sync($key, $result);

                return $this->math->round($result);
            }
        );
    }
}
