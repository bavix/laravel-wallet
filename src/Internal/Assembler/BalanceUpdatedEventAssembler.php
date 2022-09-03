<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\BalanceUpdatedEvent;
use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Models\Wallet;

final class BalanceUpdatedEventAssembler implements BalanceUpdatedEventAssemblerInterface
{
    public function __construct(
        private ClockServiceInterface $clockService
    ) {
    }

    public function create(Wallet $wallet): BalanceUpdatedEventInterface
    {
        return new BalanceUpdatedEvent(
            $wallet->getKey(),
            $wallet->uuid,
            $wallet->getOriginalBalanceAttribute(),
            $this->clockService->now()
        );
    }
}
