<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Closure;
use function get_class;

/**
 * @deprecated
 * @see LockServiceInterface
 */
final class LockServiceLegacy
{
    private LockServiceInterface $lockService;

    public function __construct(LockServiceInterface $lockService)
    {
        $this->lockService = $lockService;
    }

    /**
     * @return mixed
     */
    public function lock(object $self, Closure $closure)
    {
        assert(method_exists($self, 'getKey'));
        $key = $self->getKey();
        if (method_exists($self, 'getUuid')) {
            $key = $self->getUuid();
        }

        return $this->lockService->block(
            'legacy_'.get_class($self).$key,
            $closure
        );
    }
}
