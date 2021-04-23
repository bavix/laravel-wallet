<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\CacheInterface;
use Bavix\Wallet\Settings\CacheSetting;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Psr\SimpleCache\InvalidArgumentException;

class CacheService implements CacheInterface
{
    private CacheRepository $cacheService;

    public function __construct(
        CacheManagerService $cacheManagerService,
        CacheSetting $cacheSetting
    ) {
        $this->cacheService = $cacheManagerService->getRepository(
            $cacheSetting->getDriver(),
            $cacheSetting->getTags(),
        );
    }

    /**
     * @param null|mixed $default
     *
     * @throws InvalidArgumentException
     */
    public function get(string $key, $default = null)
    {
        return $this->cacheService->get($key, $default);
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function set(string $key, $value): bool
    {
        return $this->cacheService->set($key, (string) $value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function missing(string $key): bool
    {
        return !$this->cacheService->has($key);
    }

    public function increment(string $key, $value)
    {
        return $this->cacheService->increment($key, (string) $value);
    }

    public function forget(string $key): bool
    {
        return $this->cacheService->forget($key);
    }
}
