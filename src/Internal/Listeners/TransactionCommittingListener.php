<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Listeners;

use Bavix\Wallet\Internal\Service\ConnectionServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;

final class TransactionCommittingListener
{
    /**
     * This listener is responsible for performing actions when a transaction is successfully committed.
     *
     * It checks the transaction level from the database connection and if it is 1 (indicating the top level of the transaction),
     * it calls the `committing` method of the `RegulatorServiceInterface` to perform actions like updating the transaction status in the database.
     *
     * @see ConnectionServiceInterface::get()
     * @see ConnectionInterface::transactionLevel()
     * @see RegulatorServiceInterface::committing()
     */
    public function __invoke(): void
    {
        // Get the database connection
        // This service is responsible for getting the database connection.
        $connection = app(ConnectionServiceInterface::class)->get();

        // Check if the transaction level is 1 indicating the top level of the transaction
        // The transaction level represents the nesting level of the transaction.
        // The top level of the transaction is 1, indicating that the current transaction is the outermost transaction.
        if ($connection->transactionLevel() === 1) {
            // Call the `committing` method of the `RegulatorServiceInterface`
            // This method is responsible for performing actions when a transaction is successfully committed.
            // It is typically used to update the transaction status in the database.
            // The `committing` method is called to perform actions like updating the transaction status in the database.
            app(RegulatorServiceInterface::class)->committing();
        }
    }
}
