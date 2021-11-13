<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class StorageService implements StorageServiceInterface
{
    private CacheRepository $cache;

    private LockServiceInterface $lock;

    private MathServiceInterface $math;

    public function __construct(
        CacheManager $cacheManager,
        ConfigRepository $config,
        LockServiceInterface $lock,
        MathServiceInterface $math
    ) {
        $this->math = $math;
        $this->lock = $lock;
        $this->cache = $cacheManager->driver(
            $config->get('wallet.cache.driver', 'array')
        );
    }

    public function flush(): bool
    {
        return $this->cache->clear();
    }

    public function missing(string $key): bool
    {
        return $this->cache->forget($key);
    }

    public function get(string $key): string
    {
        $value = $this->cache->get($key);
        if ($value === null) {
            throw new RecordNotFoundException(
                'The repository did not find the object',
                ExceptionInterface::RECORD_NOT_FOUND
            );
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
            $key.'::increase',
            function () use ($key, $value): string {
                $result = $this->math->add($this->get($key), $value);
                $this->sync($key, $result);

                return $this->math->round($result);
            }
        );
    }
}
