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
        $value = $this->cacheRepository->get(self::PREFIX . $uuid);
        if ($value === null) {
            throw new RecordNotFoundException(
                'The repository did not find the object',
                ExceptionInterface::RECORD_NOT_FOUND
            );
        }

        return $this->mathService->round($value);
    }

    public function sync(string $uuid, float|int|string $value): bool
    {
        return $this->cacheRepository->forever(self::PREFIX . $uuid, $this->mathService->round($value));
    }

    /**
     * @throws RecordNotFoundException
     */
    public function increase(string $uuid, float|int|string $value): string
    {
        $result = $this->mathService->add($this->get($uuid), $value);
        $this->sync($uuid, $this->mathService->round($result));

        return $this->mathService->round($result);
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
        $results = [];
        foreach ($uuids as $uuid) {
            $results[$uuid] = $this->get($uuid);
        }

        return $results;
    }

    /**
     * @param non-empty-array<string, float|int|string> $inputs
     */
    public function multiSync(array $inputs): bool
    {
        foreach ($inputs as $uuid => $value) {
            $this->sync($uuid, $value);
        }

        return true;
    }

    /**
     * @template T of non-empty-array<string, float|int|string>
     *
     * @param T $inputs
     *
     * @return non-empty-array<value-of<T>, string>
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function multiIncrease(array $inputs): array
    {
        $results = [];
        foreach ($inputs as $uuid => $value) {
            $results[$uuid] = $this->increase($uuid, $value);
        }

        return $results;
    }
}
