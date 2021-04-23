<?php

declare(strict_types=1);

namespace Bavix\Wallet\Settings;

use Illuminate\Config\Repository;

class LockSetting
{
    private ?string $driver;

    private array $tags = ['bavix-wallet-lock'];

    private int $seconds;

    public function __construct(Repository $repository)
    {
        $this->seconds = (int) $repository->get('wallet.lock.seconds', 1);
        $this->driver = $repository->get('wallet.lock.driver');
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

    public function getSeconds(): int
    {
        return $this->seconds;
    }
}
