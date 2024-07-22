<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Listeners;

use Bavix\Wallet\Internal\Service\ConnectionServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;

final class TransactionCommittedListener
{
    /**
     * This listener is responsible for performing actions after a transaction has been successfully committed.
     *
     * It checks the transaction level from the database connection and if it is 0 (indicating the top level of the transaction),
     * it calls the `committed` method of the `RegulatorServiceInterface` to perform actions like updating the transaction status in the database.
     *
     * @see ConnectionServiceInterface::get()
     * @see ConnectionInterface::transactionLevel()
     * @see RegulatorServiceInterface::committed()
     */
    public function __invoke(): void
    {
        // Get the database connection
        $connection = app(ConnectionServiceInterface::class)->get();

        // Get the transaction level from the database connection
        // The transaction level represents the nesting level of the transaction.
        // The top level of the transaction is 0, indicating that the current transaction is the outermost transaction.
        $transactionLevel = $connection->transactionLevel();

        // Check if the transaction level is 0 indicating the top level of the transaction
        if ($transactionLevel === 0) {
            // The transaction is at the top level, so perform actions when the transaction is successfully committed

            // Call the `committed` method of the `RegulatorServiceInterface` to perform actions like updating the transaction status in the database
            // This method is responsible for performing actions after a transaction has been successfully committed.
            // It is typically used to update the transaction status in the database.
            app(RegulatorServiceInterface::class)->committed();
        }
    }
}
