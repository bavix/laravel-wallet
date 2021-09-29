<?php

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\UuidInterface;
use Bavix\Wallet\Objects\EmptyLock;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LockService
{
    /**
     * @var string
     */
    protected $ikey;

    /**
     * LockService constructor.
     */
    public function __construct(UuidInterface $uuidService)
    {
        $this->ikey = $uuidService->uuid4();
    }

    /**
     * @param object $self
     *
     * @return mixed
     */
    public function lock($self, string $name, \Closure $closure)
    {
        return $this->lockProvider($self, $name, (int) config('wallet.lock.seconds', 1))
            ->get($this->bindTo($self, $closure))
        ;
    }

    /**
     * @param object $self
     *
     * @throws
     */
    protected function bindTo($self, \Closure $closure): \Closure
    {
        $reflect = new \ReflectionFunction($closure);
        if (strpos((string) $reflect, 'static') === false) {
            return $closure->bindTo($self);
        }

        return $closure;
    }

    /**
     * @codeCoverageIgnore
     */
    protected function cache(): ?Store
    {
        try {
            return Cache::store(config('wallet.lock.cache'))
                ->getStore()
            ;
        } catch (\Throwable $throwable) {
            return null;
        }
    }

    /**
     * @param object $self
     */
    protected function lockProvider($self, string $name, int $seconds): Lock
    {
        $store = $this->cache();
        $enabled = $store && config('wallet.lock.enabled', false);

        // fixme: CodeClimate
        // @codeCoverageIgnoreStart
        if ($enabled && $store instanceof LockProvider) {
            $class = \get_class($self);
            $uniqId = $class.$this->ikey;
            if ($self instanceof Model) {
                $uniqId = $class.$self->getKey();
            }

            return $store->lock("{$name}.{$uniqId}", $seconds);
        }
        // @codeCoverageIgnoreEnd

        return app(EmptyLock::class);
    }
}
