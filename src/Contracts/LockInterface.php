<?php

declare(strict_types=1);

namespace Bavix\Wallet\Contracts;

interface LockInterface
{
    public function acquire(string $name): bool;

    public function release(string $name): bool;
}
