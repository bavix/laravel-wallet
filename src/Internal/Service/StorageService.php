<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\RecordNotFoundException;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

final class StorageService implements StorageServiceInterface
{
    private const PREFIX = 'wallet_strg::';

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
}
