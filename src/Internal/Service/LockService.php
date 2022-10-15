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
    private const LOCK_KEY = 'wallet_lock::';

    private const INNER_KEYS = 'inner_keys::';

    private ?LockProvider $lockProvider = null;

    private CacheRepository $lockedKeys;

    private CacheRepository $cache;

    public function __construct(
        private ConnectionServiceInterface $connectionService,
        CacheFactory $cacheFactory,
        private int $seconds
    ) {
        $this->cache = $cacheFactory->store(config('wallet.lock.driver', 'array'));
        $this->lockedKeys = $cacheFactory->store('array');
    }

    /**
     * @throws LockProviderNotFoundException
     */
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
     *
     * @throws LockProviderNotFoundException
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

    /**
     * @throws LockProviderNotFoundException
     * @codeCoverageIgnore
     */
    private function getLockProvider(): LockProvider
    {
        if ($this->lockProvider === null) {
            $store = $this->cache->getStore();
            if (! ($store instanceof LockProvider)) {
                throw new LockProviderNotFoundException(
                    'Lockable cache not found',
                    ExceptionInterface::LOCK_PROVIDER_NOT_FOUND
                );
            }

            $this->lockProvider = $store;
        }

        return $this->lockProvider;
    }
}
