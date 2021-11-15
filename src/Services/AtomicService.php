<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Service\LockServiceInterface;

final class AtomicService implements AtomicServiceInterface
{
    private const PREFIX = 'wallet_atomic::';

    private LockServiceInterface $lockService;
    private CastServiceInterface $castService;

    public function __construct(
        LockServiceInterface $lockService,
        CastServiceInterface $castService
    ) {
        $this->lockService = $lockService;
        $this->castService = $castService;
    }

    /** @return mixed */
    public function block(object $object, callable $closure)
    {
        return $this->lockService->block($this->key($object), $closure);
    }

    private function key(object $object): string
    {
        $model = $this->castService->getModel($object);

        return self::PREFIX.'::'.get_class($model).'::'.$model->getKey();
    }
}
