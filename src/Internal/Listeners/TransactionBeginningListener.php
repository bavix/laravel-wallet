<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Listeners;

use Bavix\Wallet\Internal\Service\ConnectionServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;

final class TransactionBeginningListener
{
    public function __invoke(): void
    {
        if (app(ConnectionServiceInterface::class)->get()->transactionLevel() === 1) {
            app(RegulatorServiceInterface::class)->purge();
        }
    }
}
