<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\UuidInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 * @see AtomicService
 */
class LockService
{
    private string $ikey;

    private AtomicService $atomicService;

    /**
     * LockService constructor.
     */
    public function __construct(UuidInterface $uuidService, AtomicService $atomicService)
    {
        $this->ikey = $uuidService->uuid4();
        $this->atomicService = $atomicService;
    }

    /**
     * @param object $self
     *
     * @return mixed
     */
    public function lock($self, string $name, \Closure $closure)
    {
        $class = \get_class($self);
        $uniqId = $class.$this->ikey;
        if ($self instanceof Model) {
            $uniqId = $class.$self->getKey();
        }

        return $this->atomicService->block(
            "{$name}.{$uniqId}",
            $this->bindTo($self, $closure)
        );
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
}
