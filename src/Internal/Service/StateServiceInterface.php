<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

interface StateServiceInterface
{
    public function fork(string $uuid, string $value): void;

    public function get(string $uuid): ?string;

    public function drop(string $uuid): void;
}
