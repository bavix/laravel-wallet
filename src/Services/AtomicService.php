<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\LockServiceInterface;

final class AtomicService implements AtomicServiceInterface
{
    private const PREFIX = 'wallet_atomic::';

    public function __construct(
        private DatabaseServiceInterface $databaseService,
        private LockServiceInterface $lockService,
        private CastServiceInterface $castService
    ) {
    }

    /**
     * @return mixed
     */
    public function block(Wallet $object, callable $callback)
    {
        return $this->lockService->block(
            $this->key($object),
            fn () => $this->databaseService->transaction($callback)
        );
    }

    private function key(Wallet $object): string
    {
        $wallet = $this->castService->getWallet($object);

        return self::PREFIX.'::'.$wallet::class.'::'.$wallet->uuid;
    }
}
