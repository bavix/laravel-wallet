<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Listeners;

use Bavix\Wallet\Internal\Service\ConnectionServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;

final class TransactionBeginningListener
{
    /**
     * This listener is responsible for purging all transactions and transfers
     * if it is the top level of a transaction.
     */
    public function __invoke(): void
    {
        // Get the current transaction level from the database connection
        $transactionLevel = app(ConnectionServiceInterface::class)->get()->transactionLevel();

        // If the transaction level is 1, it means it is the top level of a transaction
        if ($transactionLevel === 1) {
            // Get the regulator service instance
            /** @var RegulatorServiceInterface $regulatorService */
            $regulatorService = app(RegulatorServiceInterface::class);

            // Purge all transactions and transfers
            // This method is called to ensure that all changes made to the database within the transaction
            // are reflected in the wallet's balance. It is important to note that this action is not reversible
            // and data loss is possible.
            $regulatorService->purge();
        }
    }
}
