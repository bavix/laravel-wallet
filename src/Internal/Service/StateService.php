<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

final class StateService implements StateServiceInterface
{
    /**
     * @var array<string, string>
     */
    private array $forks = [];

    /**
     * @var array<string, callable>
     */
    private array $forkCallables = [];

    public function fork(string $uuid, callable $value): void
    {
        $this->forkCallables[$uuid] ??= $value;
    }

    public function get(string $uuid): ?string
    {
        if ($this->forkCallables[$uuid] ?? null) {
            $callable = $this->forkCallables[$uuid];
            unset($this->forkCallables[$uuid]);

            $this->forks[$uuid] = $callable();
        }

        return $this->forks[$uuid] ?? null;
    }

    public function drop(string $uuid): void
    {
        unset($this->forks[$uuid], $this->forkCallables[$uuid]);
    }
}
