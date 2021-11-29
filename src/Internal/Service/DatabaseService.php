<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\RecordsNotFoundException;
use Throwable;

final class DatabaseService implements DatabaseServiceInterface
{
    private RegulatorServiceInterface $regulatorService;
    private ConnectionInterface $connection;

    public function __construct(
        ConnectionResolverInterface $connectionResolver,
        RegulatorServiceInterface $regulatorService,
        ConfigRepository $config
    ) {
        $this->regulatorService = $regulatorService;
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

            $this->regulatorService->purge();

            return $this->connection->transaction(function () use ($callback) {
                $result = $callback();
                if ($result === false || (is_countable($result) && count($result) === 0)) {
                    $this->regulatorService->purge();
                } else {
                    $this->regulatorService->approve();
                }

                return $result;
            });
        } catch (RecordsNotFoundException|ExceptionInterface $exception) {
            $this->regulatorService->purge();

            throw $exception;
        } catch (Throwable $throwable) {
            $this->regulatorService->purge();

            throw new TransactionFailedException(
                'Transaction failed',
                ExceptionInterface::TRANSACTION_FAILED,
                $throwable
            );
        }
    }
}
