<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal;

interface LockInterface
{
    public function block(string $key, callable $callback): void;
}
