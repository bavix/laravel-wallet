<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

final class StateService implements StateServiceInterface
{
    /**
     * @var array<string, string>
     */
    private array $forks = [];

    public function fork(string $uuid, string $value): void
    {
        $this->forks[$uuid] = $value;
    }

    public function get(string $uuid): ?string
    {
        return $this->forks[$uuid] ?? null;
    }

    public function drop(string $uuid): void
    {
        unset($this->forks[$uuid]);
    }
}
