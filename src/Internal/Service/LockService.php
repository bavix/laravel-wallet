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

    private int $seconds;

    public function __construct(CacheFactory $cacheFactory)
    {
        $this->cache = $cacheFactory->store(config('wallet.lock.driver', 'array'));
        $this->seconds = (int) config('wallet.lock.seconds', 1);
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

        try {
            return $lock->block($this->seconds, $callback);
        } finally {
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
