<?php

declare(strict_types=1);

namespace Bavix\Wallet\Settings;

use Illuminate\Config\Repository;

class CacheSetting
{
    private ?string $driver;

    /** @var string[] */
    private array $tags = ['bavix-wallet'];

    private int $ttl;

    public function __construct(Repository $repository)
    {
        $this->driver = $repository->get('wallet.cache.driver');
        $this->tags = $repository->get('wallet.cache.tags', $this->tags);
        $this->ttl = (int) $repository->get('wallet.cache.ttl', 60 * 60 * 24);
    }

    public function getDriver(): ?string
    {
        return $this->driver;
    }

    /**
     * @return string[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    public function getTtl(): int
    {
        return $this->ttl;
    }
}
