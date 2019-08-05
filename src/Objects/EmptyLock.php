<?php

namespace Bavix\Wallet\Objects;

use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Str;

class EmptyLock implements Lock
{

    /**
     * Attempt to acquire the lock.
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function get($callback = null)
    {
        if ($callback === null) {
            return null;
        }

        return $callback();
    }

    /**
     * Attempt to acquire the lock for the given number of seconds.
     *
     * @param  int  $seconds
     * @param  callable|null  $callback
     * @return bool
     */
    public function block($seconds, $callback = null): bool
    {
        return true;
    }

    /**
     * Release the lock.
     *
     * @return void
     */
    public function release(): void
    {
        // lock release
    }

    /**
     * Returns the current owner of the lock.
     *
     * @return string
     */
    public function owner(): string
    {
        return Str::random();
    }

    /**
     * Releases this lock in disregard of ownership.
     *
     * @return void
     */
    public function forceRelease(): void
    {
        // force lock release
    }

}
