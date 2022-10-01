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
        private CacheRepository $cacheRepository
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
     * @param non-empty-array<string> $uuids
     *
     * @return non-empty-array<string, string>
     *
     * @throws RecordNotFoundException
     */
    public function multiGet(array $uuids): array
    {
        $keys = [];
        foreach ($uuids as $uuid) {
            $keys[self::PREFIX . $uuid] = $uuid;
        }

        $results = [];
        if (count($keys) === 1) {
            $values = [
                key($keys) => $this->cacheRepository->get(key($keys)),
            ];
        } else {
            $values = $this->cacheRepository->getMultiple(array_keys($keys));
        }

        /** @var string[] $missingKeys */
        $missingKeys = [];
        /** @var array<string, string|float|int|null> $values */
        foreach ($values as $key => $value) {
            if ($value === null) {
                $missingKeys[] = $keys[$key];
                continue;
            }

            $results[$keys[$key]] = $this->mathService->round($value);
        }

        if ($missingKeys !== []) {
            throw new RecordNotFoundException(
                'The repository did not find the object',
                ExceptionInterface::RECORD_NOT_FOUND,
                $missingKeys,
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
            $values[self::PREFIX . $uuid] = $value;
        }

        if (count($values) === 1) {
            return $this->cacheRepository->forever(key($values), current($values));
        }

        return $this->cacheRepository->setMultiple($values);
    }

    /**
     * @param non-empty-array<string, float|int|string> $inputs
     *
     * @return non-empty-array<string, string>
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function multiIncrease(array $inputs): array
    {
        $newInputs = [];
        $multiGet = $this->multiGet(array_keys($inputs));
        foreach ($multiGet as $uuid => $value) {
            $newInputs[$uuid] = $this->mathService->add($value, $inputs[$uuid]);
        }

        assert($this->multiSync($newInputs));

        return $newInputs;
    }
}
