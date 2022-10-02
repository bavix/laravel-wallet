<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class StateService implements StateServiceInterface
{
    private const PREFIX_FORKS = 'wallet_forks::';

    private const PREFIX_FORK_CALL = 'wallet_fork_call::';

    private CacheRepository $forks;

    private CacheRepository $forkCallables;

    public function __construct(CacheFactory $cacheFactory)
    {
        $this->forks = $cacheFactory->store('array');
        $this->forkCallables = $cacheFactory->store('array');
    }

    /**
     * @param string[] $uuids
     * @param callable(): array<string, string> $value
     */
    public function multiFork(array $uuids, callable $value): void
    {
        $forks = [];
        foreach ($uuids as $uuid) {
            if (! $this->forks->has(self::PREFIX_FORKS . $uuid)) {
                $forks[self::PREFIX_FORK_CALL . $uuid] = $value;
            }
        }

        if ($forks !== []) {
            $this->forkCallables->setMultiple($forks);
        }
    }

    public function get(string $uuid): ?string
    {
        $value = $this->forks->get(self::PREFIX_FORKS . $uuid);
        if ($value !== null) {
            return $value;
        }

        /** @var null|callable(): array<string, string> $callable */
        $callable = $this->forkCallables->pull(self::PREFIX_FORK_CALL . $uuid);
        if ($callable !== null) {
            $this->forks->setMultiple($callable());

            return $this->get($uuid);
        }

        return null;
    }

    public function drop(string $uuid): void
    {
        $this->forkCallables->forget(self::PREFIX_FORK_CALL . $uuid);
        $this->forks->forget(self::PREFIX_FORKS . $uuid);
    }
}
