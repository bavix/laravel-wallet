<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Contracts\BookkeeperInterface;
use Bavix\Wallet\Contracts\MathInterface;
use Psr\SimpleCache\InvalidArgumentException;

class BookkeeperService implements BookkeeperInterface
{
    private CacheService $cacheService;

    private MathInterface $mathService;

    public function __construct(
        CacheService $cacheService,
        MathInterface $mathService
    ) {
        $this->cacheService = $cacheService;
        $this->mathService = $mathService;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function missing(string $purseId): bool
    {
        return $this->cacheService->missing($purseId);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function balance(string $purseId): string
    {
        return $this->mathService->round(
            $this->cacheService->get($purseId, 0)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException
     */
    public function sync(string $purseId, $amount): bool
    {
        return $this->cacheService->set($purseId, $amount);
    }

    public function increase(string $purseId, $amount): string
    {
        return $this->mathService->round(
            (string) $this->cacheService->increment($purseId, $amount)
        );
    }
}
