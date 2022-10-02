<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class StateService implements StateServiceInterface
{
    private const PREFIX_FORKS = 'wallet_forks::';

    private const PREFIX_FORK_CALL = 'wallet_fork_call::';

    private const PREFIX_HASHMAP = 'wallet_hm::';

    private CacheRepository $store;

    public function __construct(CacheFactory $cacheFactory)
    {
        $this->store = $cacheFactory->store('array');
    }

    /**
     * @param string[] $uuids
     * @param callable(): array<string, string> $value
     */
    public function multiFork(array $uuids, callable $value): void
    {
        $forks = [];
        foreach ($uuids as $uuid) {
            if (! $this->store->has(self::PREFIX_FORKS . $uuid)) {
                $forks[self::PREFIX_FORK_CALL . $uuid] = $value;
                $forks[self::PREFIX_HASHMAP . $uuid] = $uuids;
            }
        }

        if ($forks !== []) {
            $this->store->setMultiple($forks);
        }
    }

    public function get(string $uuid): ?string
    {
        $value = $this->store->get(self::PREFIX_FORKS . $uuid);
        if ($value !== null) {
            return $value;
        }

        /** @var null|callable(): array<string, string> $callable */
        $callable = $this->store->pull(self::PREFIX_FORK_CALL . $uuid);
        if ($callable !== null) {
            /** @var array<string> $keys */
            $keys = $this->store->pull(self::PREFIX_HASHMAP . $uuid, []);
            $deleteKeys = [];
            foreach ($keys as $key) {
                $deleteKeys[] = self::PREFIX_FORK_CALL . $key;
                $deleteKeys[] = self::PREFIX_HASHMAP . $key;
            }

            if ($deleteKeys !== []) {
                $this->store->deleteMultiple($deleteKeys);
            }

            $results = $callable();
            $values = [];
            foreach ($results as $key => $value) {
                $values[self::PREFIX_FORKS . $key] = $value;
            }

            if (($values !== []) && ! $this->store->setMultiple($values)) {
                return null;
            }

            return $results[$uuid] ?? null;
        }

        return null;
    }

    public function drop(string $uuid): void
    {
        $this->store->deleteMultiple([
            self::PREFIX_FORK_CALL . $uuid,
            self::PREFIX_HASHMAP . $uuid,
            self::PREFIX_FORKS . $uuid,
        ]);
    }
}
