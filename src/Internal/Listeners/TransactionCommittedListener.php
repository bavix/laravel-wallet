<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Listeners;

use Bavix\Wallet\Internal\Service\ConnectionServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;

final class TransactionCommittedListener
{
    public function __invoke(): void
    {
        if (app(ConnectionServiceInterface::class)->get()->transactionLevel() === 0) {
            app(RegulatorServiceInterface::class)->committed();
        }
    }
}
