<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Closure;
use function get_class;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 * @see AtomicService
 */
class LockService
{
    private AtomicService $atomicService;

    public function __construct(AtomicService $atomicService)
    {
        $this->atomicService = $atomicService;
    }

    /**
     * @return mixed
     */
    public function lock(Model $self, Closure $closure)
    {
        return $this->atomicService->block(
            'legacy_'.get_class($self).$self->getKey(),
            $closure
        );
    }
}
