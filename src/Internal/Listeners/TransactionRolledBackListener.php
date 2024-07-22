<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Listeners;

use Bavix\Wallet\Internal\Service\ConnectionServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;

final class TransactionRolledBackListener
{
    /**
     * This listener is responsible for purging the regulator service
     * if the current transaction level is 0.
     *
     * The transaction level represents the nesting level of the transaction.
     * The top level of the transaction is 0, indicating that the current transaction is the outermost transaction.
     */
    public function __invoke(): void
    {
        // Get the database connection
        // This service is responsible for getting the database connection.
        // The database connection is used to communicate with the database.
        $connection = app(ConnectionServiceInterface::class)->get();

        // Get the current transaction level from the database connection
        // The transaction level represents the nesting level of the transaction.
        // The top level of the transaction is 0, indicating that the current transaction is the outermost transaction.
        $transactionLevel = $connection->transactionLevel();

        // If the transaction level is 0, it means it is the top level of a transaction
        if ($transactionLevel === 0) {
            // Call the `purge` method of the `RegulatorServiceInterface`
            // This method is responsible for purging the regulator service.
            // It clears any cached data related to the wallet.
            $regulatorService = app(RegulatorServiceInterface::class);

            // Purge the regulator service
            // This method clears any cached data related to the wallet.
            // It is necessary to purge the regulator service when the current transaction is the outermost transaction.
            $regulatorService->purge();
        }
    }
}
