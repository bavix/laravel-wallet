<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\BookkeeperInterface;
use Bavix\Wallet\Interfaces\Mathable;
use Illuminate\Cache\Repository as CacheRepository;
use Psr\SimpleCache\InvalidArgumentException;

class BookkeeperService implements BookkeeperInterface
{
    private CacheRepository $cacheRepository;

    private Mathable $mathService;

    public function __construct(
        CacheRepository $cacheRepository,
        Mathable $mathService
    ) {
        $this->cacheRepository = $cacheRepository;
        $this->mathService = $mathService;
    }

    /** {@inheritdoc} */
    public function balance(string $purseId): string
    {
        return $this->mathService->round(
            $this->cacheRepository->get($purseId, 0)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function sync(string $purseId, $amount): bool
    {
        return $this->cacheRepository->set($purseId, $amount);
    }

    public function increase(string $purseId, $amount): string
    {
        return $this->mathService->round(
            (string) $this->cacheRepository->increment($purseId, $amount)
        );
    }
}
