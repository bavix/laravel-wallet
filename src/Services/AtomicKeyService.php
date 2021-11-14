<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

final class AtomicKeyService implements AtomicKeyServiceInterface
{
    private const PREFIX = 'wallet_atomic::';

    private CastServiceInterface $castService;

    public function __construct(CastServiceInterface $castService)
    {
        $this->castService = $castService;
    }

    public function getIdentifier(object $object): string
    {
        $model = $this->castService->getModel($object);

        return self::PREFIX.'::'.get_class($model).'::'.$model->getKey();
    }
}
