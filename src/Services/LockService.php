<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Objects\EmptyLock;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Support\Facades\Cache;

class LockService
{

    /**
     * @param object $self
     * @param string $name
     * @param \Closure $closure
     * @return mixed
     */
    public function lock($self, string $name, \Closure $closure)
    {
        return $this->lockProvider($name, (int)config('wallet.lock.seconds'))
            ->get($this->bindTo($self, $closure));
    }

    /**
     * @param object $self
     * @param \Closure $closure
     * @return \Closure
     */
    protected function bindTo($self, \Closure $closure): \Closure
    {
        try {
            return $closure->bindTo($self);
        } catch (\Throwable $throwable) {
            return $closure;
        }
    }

    /**
     * @param string $name
     * @param int $seconds
     * @return Lock
     */
    protected function lockProvider(string $name, int $seconds): Lock
    {
        /**
         * @var LockProvider $store
         */
        $store = Cache::getStore();

        if ($store instanceof LockProvider) {
            return $store->lock($name, $seconds);
        }

        return new EmptyLock();
    }

}
