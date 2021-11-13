<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Exceptions\TransactionFailedException;
use Closure;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\RecordsNotFoundException;
use Throwable;

final class DatabaseService implements DatabaseServiceInterface
{
    private ConnectionInterface $connection;

    public function __construct(
        ConnectionResolverInterface $connectionResolver,
        ConfigRepository $config
    ) {
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
    public function transaction(Closure $closure)
    {
        try {
            if ($this->connection->transactionLevel() > 0) {
                return $closure();
            }

            return $this->connection->transaction($closure);
        } catch (RecordsNotFoundException|ExceptionInterface $exception) {
            throw $exception;
        } catch (Throwable $throwable) {
            throw new TransactionFailedException(
                'Transaction failed',
                ExceptionInterface::TRANSACTION_FAILED,
                $throwable
            );
        }
    }
}
