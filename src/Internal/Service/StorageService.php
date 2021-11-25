<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class StorageService implements StorageServiceInterface
{
    private LockServiceInterface $lockService;
    private MathServiceInterface $mathService;
    private CacheRepository $cache;

    public function __construct(
        CacheManager $cacheManager,
        ConfigRepository $config,
        LockServiceInterface $lockService,
        MathServiceInterface $mathService
    ) {
        $this->mathService = $mathService;
        $this->lockService = $lockService;
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

    /** @throws RecordNotFoundException */
    public function get(string $key): string
    {
        $value = $this->cache->get($key);
        if ($value === null) {
            throw new RecordNotFoundException(
                'The repository did not find the object',
                ExceptionInterface::RECORD_NOT_FOUND
            );
        }

        return $this->mathService->round($value);
    }

    public function sync(string $key, $value): bool
    {
        return $this->cache->set($key, $value);
    }

    /**
     * @param float|int|string $value
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function increase(string $key, $value): string
    {
        return $this->lockService->block(
            $key.'::increase',
            function () use ($key, $value): string {
                $result = $this->mathService->add($this->get($key), $value);
                $this->sync($key, $result);

                return $this->mathService->round($result);
            }
        );
    }
}
