<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final readonly class StorageService implements StorageServiceInterface
{
    private const string PREFIX = 'wallet_sg::';

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

    public function forget(string $uuid): bool
    {
        return $this->cacheRepository->forget(self::PREFIX.$uuid);
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
     * @return non-empty-string
     *
     * @throws RecordNotFoundException
     */
    public function increase(string $uuid, float|int|string $value): string
    {
        /** @var non-empty-string $result */
        $result = current($this->multiIncrease([
            $uuid => $value,
        ]));

        return $result;
    }

    /**
     * @template T of non-empty-array<non-empty-string>
     *
     * @param T $uuids
     * @return non-empty-array<value-of<T>, non-empty-string>
     *
     * @throws RecordNotFoundException
     */
    public function multiGet(array $uuids): array
    {
        $keys = [];
        foreach ($uuids as $uuid) {
            $keys[self::PREFIX.$uuid] = $uuid;
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
        /** @var array<float|int|non-empty-string|null> $values */
        foreach ($values as $key => $value) {
            $uuid = $keys[$key];
            if ($value === null) {
                $missingKeys[] = $uuid;

                continue;
            }

            /** @var float|int|non-empty-string $valueToRound */
            $valueToRound = $value;
            $rounded = $this->mathService->round($valueToRound);
            $results[$uuid] = $rounded;
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
            /** @var float|int|non-empty-string $valueToRound */
            $valueToRound = $value;
            $rounded = $this->mathService->round($valueToRound);
            $values[self::PREFIX.$uuid] = $rounded;
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
     * @return non-empty-array<key-of<T>, string>
     *
     * @throws RecordNotFoundException
     */
    public function multiIncrease(array $inputs): array
    {
        $newInputs = [];
        /** @var non-empty-array<non-empty-string> $uuids */
        $uuids = array_keys($inputs);
        $multiGet = $this->multiGet($uuids);
        foreach ($uuids as $uuid) {
            /** @var non-empty-string $multiGetValue */
            $multiGetValue = $multiGet[$uuid];
            /** @var float|int|non-empty-string $inputValue */
            $inputValue = $inputs[$uuid];
            $added = $this->mathService->add($multiGetValue, $inputValue);
            $rounded = $this->mathService->round($added);
            $newInputs[$uuid] = $rounded;
        }

        $this->multiSync($newInputs);

        /** @var non-empty-array<key-of<T>, non-empty-string> $newInputs */
        return $newInputs;
    }
}
