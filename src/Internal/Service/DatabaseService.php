<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Bavix\Wallet\Internal\Exceptions\TransactionStartException;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\RecordsNotFoundException;
use Throwable;

final class DatabaseService implements DatabaseServiceInterface
{
    private ConnectionInterface $connection;

    private bool $init = false;

    public function __construct(
        ConnectionResolverInterface $connectionResolver,
        private RegulatorServiceInterface $regulatorService
    ) {
        $this->connection = $connectionResolver->connection(config('wallet.database.connection'));
    }

    /**
     * @throws RecordsNotFoundException
     * @throws TransactionFailedException
     * @throws ExceptionInterface
     */
    public function transaction(callable $callback): mixed
    {
        $level = $this->connection->transactionLevel();
        if ($level > 0 && ! $this->init) {
            throw new TransactionStartException(
                'Working inside an embedded transaction is not possible. https://bavix.github.io/laravel-wallet/#/transaction',
                ExceptionInterface::TRANSACTION_START,
            );
        }

        if ($level > 0) {
            return $callback();
        }

        $this->init = true;

        try {
            $this->regulatorService->purge();

            return $this->connection->transaction(function () use ($callback) {
                $result = $callback();
                $this->init = false;

                if ($result === false) {
                    return false;
                }

                if (is_countable($result) && count($result) === 0) {
                    return $result;
                }

                $this->regulatorService->approve();

                return $result;
            });
        } catch (RecordsNotFoundException|ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            throw new TransactionFailedException(
                'Transaction failed. Message: ' . $throwable->getMessage(),
                ExceptionInterface::TRANSACTION_FAILED,
                $throwable
            );
        } finally {
            $this->regulatorService->purge();
            $this->init = false;
        }
    }
}
