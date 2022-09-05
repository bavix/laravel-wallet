<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class LockService implements LockServiceInterface
{
    private const PREFIX = 'wallet_lock::';

    /**
     * @var array<string, bool>
     */
    private array $lockedKeys = [];

    private CacheRepository $cache;

    private int $seconds;

    public function __construct(CacheFactory $cacheFactory)
    {
        $this->seconds = (int) config('wallet.lock.seconds', 1);
        $this->cache = $cacheFactory->store(config('wallet.lock.driver', 'array'));
    }

    /**
     * @throws LockProviderNotFoundException
     */
    public function block(string $key, callable $callback): mixed
    {
        if ($this->isBlocked($key)) {
            return $callback();
        }

        $this->lockedKeys[$key] = true;

        try {
            return $this->getLockProvider()
                ->lock(self::PREFIX . $key)
                ->block($this->seconds, $callback)
            ;
        } finally {
            unset($this->lockedKeys[$key]);
        }
    }

    public function isBlocked(string $key): bool
    {
        return $this->lockedKeys[$key] ?? false;
    }

    /**
     * @throws LockProviderNotFoundException
     * @codeCoverageIgnore
     */
    private function getLockProvider(): LockProvider
    {
        $store = $this->cache->getStore();
        if ($store instanceof LockProvider) {
            return $store;
        }

        throw new LockProviderNotFoundException(
            'Lockable cache not found',
            ExceptionInterface::LOCK_PROVIDER_NOT_FOUND
        );
    }
}
