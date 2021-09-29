<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\LockInterface;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

class AtomicService implements LockInterface
{
    public function block(string $key, callable $callback): void
    {
        try {
            $seconds = 1; // fixme: from config
            Cache::lock($key)->block($seconds, $callback);
        } catch (LockTimeoutException $lockTimeoutException) {
            // fixme
        }
    }
}
