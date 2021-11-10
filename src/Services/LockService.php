<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\UuidInterface;
use Closure;
use Illuminate\Database\Eloquent\Model;
use ReflectionException;
use ReflectionFunction;
use function get_class;

/**
 * @deprecated
 * @see AtomicService
 */
class LockService
{
    private string $ikey;
    private AtomicService $atomicService;

    public function __construct(UuidInterface $uuidService, AtomicService $atomicService)
    {
        $this->ikey = $uuidService->uuid4();
        $this->atomicService = $atomicService;
    }

    public function lock(object $self, string $name, Closure $closure)
    {
        $class = get_class($self);
        $uniqId = $class.$this->ikey;
        if ($self instanceof Model) {
            $uniqId = $class.$self->getKey();
        }

        return $this->atomicService->block(
            "legacy_{$name}.{$uniqId}",
            $this->bindTo($self, $closure)
        );
    }

    /**
     * @throws ReflectionException
     */
    protected function bindTo(object $self, Closure $closure): Closure
    {
        $reflect = new ReflectionFunction($closure);
        if (strpos((string) $reflect, 'static') === false) {
            return $closure->bindTo($self);
        }

        return $closure;
    }
}
