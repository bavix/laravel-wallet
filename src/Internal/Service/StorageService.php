<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class StorageService implements StorageServiceInterface
{
    private const PREFIX = 'wallet_sg::';

    public function __construct(
        private MathServiceInterface $mathService,
        private CacheRepository $cacheRepository,
        private ?int $ttl
    ) {
    }

    public function flush(): bool
    {
        return $this->cacheRepository->clear();
    }

    public function missing(string $uuid): bool
    {
        return $this->cacheRepository->forget(self::PREFIX . $uuid);
    }

    /**
     * @throws RecordNotFoundException
     */
    public function get(string $uuid): string
    {
        return current($this->multiGet([$uuid]));
    }

    public function sync(string $uuid, float|int|string $value): bool
    {
        return $this->multiSync([
            $uuid => $value,
        ]);
    }

    /**
     * @throws RecordNotFoundException
     */
    public function increase(string $uuid, float|int|string $value): string
    {
        return current($this->multiIncrease([
            $uuid => $value,
        ]));
    }

    /**
     * @template T of non-empty-array<string>
     *
     * @param T $uuids
     *
     * @return non-empty-array<value-of<T>, string>
     *
     * @throws RecordNotFoundException
     */
    public function multiGet(array $uuids): array
    {
        $keys = [];
        foreach ($uuids as $uuid) {
            $keys[self::PREFIX . $uuid] = $uuid;
        }

        $missingKeys = [];
        if (count($keys) === 1) {
            $values = [];
            foreach (array_keys($keys) as $key) {
                $values[$key] = $this->cacheRepository->get($key);
            }
        } else {
            $values = $this->cacheRepository->getMultiple(array_keys($keys));
        }

        $results = [];
        /** @var array<float|int|string|null> $values */
        foreach ($values as $key => $value) {
            $uuid = $keys[$key];
            if ($value === null) {
                $missingKeys[] = $uuid;
                continue;
            }

            $results[$uuid] = $this->mathService->round($value);
        }

        if ($missingKeys !== []) {
            throw new RecordNotFoundException(
                'The repository did not find the object',
                ExceptionInterface::RECORD_NOT_FOUND,
                $missingKeys
            );
        }

        assert($results !== []);

        return $results;
    }

    /**
     * @param non-empty-array<string, float|int|string> $inputs
     */
    public function multiSync(array $inputs): bool
    {
        $values = [];
        foreach ($inputs as $uuid => $value) {
            $values[self::PREFIX . $uuid] = $this->mathService->round($value);
        }

        if (count($values) === 1) {
            return $this->cacheRepository->set(key($values), current($values), $this->ttl);
        }

        return $this->cacheRepository->setMultiple($values, $this->ttl);
    }

    /**
     * @template T of non-empty-array<string, float|int|string>
     *
     * @param T $inputs
     *
     * @return non-empty-array<key-of<T>, string>
     * @psalm-return non-empty-array<string, string>
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function multiIncrease(array $inputs): array
    {
        $newInputs = [];
        $uuids = array_keys($inputs);
        $multiGet = $this->multiGet($uuids);
        foreach ($uuids as $uuid) {
            $newInputs[$uuid] = $this->mathService->round($this->mathService->add($multiGet[$uuid], $inputs[$uuid]));
        }

        $this->multiSync($newInputs);

        assert($newInputs !== []);

        return $newInputs;
    }
}
