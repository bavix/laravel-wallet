<?php

declare(strict_types=1);

namespace Bavix\Wallet\Settings;

use Illuminate\Config\Repository;

class CacheSetting
{
    private ?string $driver;

    /** @var string[] */
    private array $tags = ['bavix-wallet'];

    public function __construct(Repository $repository)
    {
        $this->driver = $repository->get('wallet.cache.driver');
        $this->tags = $repository->get('wallet.cache.tags', $this->tags);
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
}
