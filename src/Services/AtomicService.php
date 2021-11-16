<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Illuminate\Database\RecordsNotFoundException;

final class AtomicService implements AtomicServiceInterface
{
    private const PREFIX = 'wallet_atomic::';

    private DatabaseServiceInterface $databaseService;
    private LockServiceInterface $lockService;
    private CastServiceInterface $castService;

    public function __construct(
        DatabaseServiceInterface $databaseService,
        LockServiceInterface $lockService,
        CastServiceInterface $castService
    ) {
        $this->databaseService = $databaseService;
        $this->lockService = $lockService;
        $this->castService = $castService;
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
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

        return self::PREFIX.'::'.get_class($wallet).'::'.$wallet->uuid;
    }
}
