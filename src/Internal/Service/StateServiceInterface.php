<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface StateServiceInterface
{
    /**
     * @param callable(): string $value
     */
    public function fork(string $uuid, callable $value): void;

    public function get(string $uuid): ?string;

    public function drop(string $uuid): void;
}
