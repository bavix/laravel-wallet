<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Services\StateServiceInterface;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\RecordsNotFoundException;
use Throwable;

final class DatabaseService implements DatabaseServiceInterface
{
    private StateServiceInterface $stateService;
    private ConnectionInterface $connection;

    public function __construct(
        StateServiceInterface $stateService,
        ConnectionResolverInterface $connectionResolver,
        ConfigRepository $config
    ) {
        $this->stateService = $stateService;
        $this->connection = $connectionResolver->connection(
            $config->get('wallet.database.connection')
        );
    }

    /**
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     *
     * @return mixed
     */
    public function transaction(callable $callback)
    {
        try {
            if ($this->connection->transactionLevel() > 0) {
                return $callback();
            }

            $this->stateService->purge();

            return $this->connection->transaction(function () use ($callback) {
                $result = $callback();
                $this->stateService->commit();

                return $result;
            });
        } catch (RecordsNotFoundException|ExceptionInterface $exception) {
            $this->stateService->purge();

            throw $exception;
        } catch (Throwable $throwable) {
            $this->stateService->purge();

            throw new TransactionFailedException(
                'Transaction failed',
                ExceptionInterface::TRANSACTION_FAILED,
                $throwable
            );
        }
    }
}
