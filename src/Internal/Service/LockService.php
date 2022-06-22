<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class LockService implements LockServiceInterface
{
    private CacheRepository $cache;

    private int $seconds;

    public function __construct(CacheFactory $cacheFactory, ConfigRepository $config)
    {
        $this->seconds = (int) $config->get('wallet.lock.seconds', 1);
        $this->cache = $cacheFactory->store($config->get('wallet.lock.driver', 'array'));
    }

    /**
     * @throws LockProviderNotFoundException
     */
    public function block(string $key, callable $callback): mixed
    {
        return $this->getLockProvider()
            ->lock($key)
            ->block($this->seconds, $callback)
        ;
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
