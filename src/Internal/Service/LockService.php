<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class LockService implements LockServiceInterface
{
    private const LOCK_KEY = 'wallet_lock::';

    private const INNER_KEYS = 'inner_keys::';

    private ?LockProvider $lockProvider = null;

    private readonly CacheRepository $lockedKeys;

    private readonly CacheRepository $cache;

    public function __construct(
        private readonly ConnectionServiceInterface $connectionService,
        CacheFactory $cacheFactory,
        private readonly int $seconds
    ) {
        $this->cache = $cacheFactory->store(config('wallet.lock.driver', 'array'));
        $this->lockedKeys = $cacheFactory->store('array');
    }

    public function block(string $key, callable $callback): mixed
    {
        if ($this->isBlocked($key)) {
            return $callback();
        }

        $lock = $this->getLockProvider()
            ->lock(self::LOCK_KEY . $key, $this->seconds);
        $this->lockedKeys->put(self::INNER_KEYS . $key, true, $this->seconds);

        // let's release the lock after the transaction, the fight against the race
        if ($this->connectionService->get()->transactionLevel() > 0) {
            $lock->block($this->seconds);

            return $callback();
        }

        try {
            return $lock->block($this->seconds, $callback);
        } finally {
            $this->lockedKeys->delete(self::INNER_KEYS . $key);
        }
    }

    /**
     * @template T
     * @param string[] $keys
     * @param callable(): T $callback
     *
     * @return T
     */
    public function blocks(array $keys, callable $callback): mixed
    {
        $callable = $callback;
        foreach ($keys as $key) {
            if (! $this->isBlocked($key)) {
                $callable = fn () => $this->block($key, $callable);
            }
        }

        return $callable();
    }

    public function releases(array $keys): void
    {
        $lockProvider = $this->getLockProvider();

        foreach ($keys as $key) {
            if (! $this->isBlocked($key)) {
                continue;
            }

            $lockProvider
                ->lock(self::LOCK_KEY . $key, $this->seconds)
                ->forceRelease();

            $this->lockedKeys->delete(self::INNER_KEYS . $key);
        }
    }

    public function isBlocked(string $key): bool
    {
        return $this->lockedKeys->get(self::INNER_KEYS . $key) === true;
    }

    private function getLockProvider(): LockProvider
    {
        if (! $this->lockProvider instanceof LockProvider) {
            $store = $this->cache->getStore();
            assert($store instanceof LockProvider);

            $this->lockProvider = $store;
        }

        return $this->lockProvider;
    }
}
