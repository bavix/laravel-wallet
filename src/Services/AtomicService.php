<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\LockProviderNotFoundException;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Internal\Service\StateServiceInterface;
use Illuminate\Database\RecordsNotFoundException;

/**
 * @internal
 */
final class AtomicService implements AtomicServiceInterface
{
    public function __construct(
        private DatabaseServiceInterface $databaseService,
        private StateServiceInterface $stateService,
        private LockServiceInterface $lockService,
        private CastServiceInterface $castService
    ) {
    }

    /**
     * @param non-empty-array<Wallet> $objects
     *
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function blocks(array $objects, callable $callback): mixed
    {
        $callable = fn () => $this->databaseService->transaction($callback);
        foreach ($objects as $object) {
            $wallet = $this->castService->getWallet($object);
            $callable = fn () => $this->lockService->block(
                $wallet->uuid,
                function () use ($wallet, $callable) {
                    try {
                        $this->stateService->fork($wallet->uuid, $wallet->balance);
                        return $callable();
                    } finally {
                        $this->stateService->drop($wallet->uuid);
                    }
                }
            );
        }

        return $callable();
    }

    /**
     * @throws LockProviderNotFoundException
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function block(Wallet $object, callable $callback): mixed
    {
        return $this->blocks([$object], $callback);
    }
}
