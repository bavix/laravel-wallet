<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\BalanceUpdatedEvent;
use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Models\Wallet;

final class BalanceUpdatedEventAssembler implements BalanceUpdatedEventAssemblerInterface
{
    private ClockServiceInterface $clockService;

    public function __construct(ClockServiceInterface $clockService)
    {
        $this->clockService = $clockService;
    }

    public function create(Wallet $wallet): BalanceUpdatedEventInterface
    {
        return new BalanceUpdatedEvent(
            (int) $wallet->getKey(),
            $wallet->uuid,
            $wallet->getOriginalBalanceAttribute(),
            $this->clockService->now()
        );
    }
}
