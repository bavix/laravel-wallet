<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Objects\EmptyLock;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Database\Eloquent\Model;
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
        return $this->lockProvider($self, $name, (int)config('wallet.lock.seconds'))
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
     * @param object $self
     * @param string $name
     * @param int $seconds
     * @return Lock
     */
    protected function lockProvider($self, string $name, int $seconds): Lock
    {
        /**
         * @var LockProvider $store
         */
        $store = Cache::getStore();
        $enabled = config('wallet.lock.enabled', false);

        if ($enabled && $store instanceof LockProvider) {
            $uniqId = \get_class($self);
            if ($self instanceof Model) {
                $uniqId .= $self->getKey();
            }

            return $store->lock("$name.$uniqId", $seconds);
        }

        return new EmptyLock();
    }

}
