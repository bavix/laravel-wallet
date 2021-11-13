<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\LockInterface;
use Closure;
use function get_class;
use Illuminate\Database\Eloquent\Model;

/**
 * @deprecated
 * @see LockInterface
 */
final class LockService
{
    private LockInterface $lockService;

    public function __construct(LockInterface $lockService)
    {
        $this->lockService = $lockService;
    }

    /**
     * @return mixed
     */
    public function lock(Model $self, Closure $closure)
    {
        return $this->lockService->block(
            'legacy_'.get_class($self).$self->getKey(),
            $closure
        );
    }
}
