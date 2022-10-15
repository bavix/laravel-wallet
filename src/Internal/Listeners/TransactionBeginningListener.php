<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Listeners;

use Bavix\Wallet\Internal\Service\ConnectionServiceInterface;
use Bavix\Wallet\Services\RegulatorServiceInterface;

final class TransactionBeginningListener
{
    public function __construct(
        private ConnectionServiceInterface $connectionService,
        private RegulatorServiceInterface $regulatorService
    ) {
    }

    public function __invoke(): void
    {
        if ($this->connectionService->get()->transactionLevel() === 1) {
            $this->regulatorService->purge();
        }
    }
}
