<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;

/**
 * @internal
 */
final readonly class ConnectionService implements ConnectionServiceInterface
{
    private ConnectionInterface $connection;

    public function __construct(ConnectionResolverInterface $connectionResolver)
    {
        /** @var string|null $connectionName */
        $connectionName = config('wallet.database.connection');
        $this->connection = $connectionResolver->connection($connectionName);
    }

    public function get(): ConnectionInterface
    {
        return $this->connection;
    }
}
