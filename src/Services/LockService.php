<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Objects\EmptyLock;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class LockService
{

    /**
     * @var string
     */
    protected $uniqId;

    /**
     * LockService constructor.
     */
    public function __construct()
    {
        $this->uniqId = Str::random();
    }

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
        try {
            /**
             * @var LockProvider $store
             */
            $store = Cache::getStore();
            $enabled = config('wallet.lock.enabled', false);

            if ($enabled && $store instanceof LockProvider) {
                $class = \get_class($self);
                $uniqId = $class . $this->uniqId;
                if ($self instanceof Model) {
                    $uniqId = $class . $self->getKey();
                }

                return $store->lock("$name.$uniqId", $seconds);
            }
        } catch (\Throwable $throwable) {
            // write error's
        }

        return new EmptyLock();
    }

}
