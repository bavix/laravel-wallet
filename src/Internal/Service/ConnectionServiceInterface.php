<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Service;

use Illuminate\Database\ConnectionInterface;

/**
 * Interface ConnectionServiceInterface.
 *
 * This interface defines the contract for retrieving a database connection.
 *
 * @see https://laravel.com/docs/11.x/database#running-queries
 * @see https://laravel.com/docs/11.x/database#database-transactions
 * @see https://laravel.com/docs/11.x/database#introduction
 */
interface ConnectionServiceInterface
{
    /**
     * Get a database connection instance.
     *
     * This method returns a database connection instance.
     *
     * @return ConnectionInterface The database connection instance.
     *
     * @example
     *     // Get a database connection instance.
     *     $connection = $this->get();
     *
     *     // Run a query on the "users" table.
     *     * $connection->table('users')->where('id', 1)->update(['name' => 'Jane']);
     *
     *     // Start a database transaction.
     *     $connection->beginTransaction();
     *
     *     try {
     *         // Run queries...
     *
     *         // Commit the transaction.
     *         $connection->commit();
     *     } catch (Exception $e) {
     *         // Rollback the transaction.
     *         $connection->rollback();
     *
     *         // Rethrow the exception.
     *         throw $e;
     *     }
     */
    public function get(): ConnectionInterface;
}
