<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class StorageService implements StorageServiceInterface
{
    private LockServiceInterface $lockService;
    private MathServiceInterface $mathService;
    private CacheRepository $cacheRepository;

    public function __construct(
        LockServiceInterface $lockService,
        MathServiceInterface $mathService,
        CacheRepository $cacheRepository
    ) {
        $this->cacheRepository = $cacheRepository;
        $this->mathService = $mathService;
        $this->lockService = $lockService;
    }

    public function flush(): bool
    {
        return $this->cacheRepository->clear();
    }

    public function missing(string $key): bool
    {
        return $this->cacheRepository->forget($key);
    }

    /** @throws RecordNotFoundException */
    public function get(string $key): string
    {
        $value = $this->cacheRepository->get($key);
        if ($value === null) {
            throw new RecordNotFoundException(
                'The repository did not find the object',
                ExceptionInterface::RECORD_NOT_FOUND
            );
        }

        return $this->mathService->round($value);
    }

    /** @param float|int|string $value */
    public function sync(string $key, $value): bool
    {
        return $this->cacheRepository->set($key, $value);
    }

    /**
     * @param float|int|string $value
     *
     * @throws LockProviderNotFoundException
     * @throws RecordNotFoundException
     */
    public function increase(string $key, $value): string
    {
        return $this->lockService->block(
            $key.'::increase',
            function () use ($key, $value): string {
                $result = $this->mathService->add($this->get($key), $value);
                $this->sync($key, $result);

                return $this->mathService->round($result);
            }
        );
    }
}
