<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\WalletCreatedEvent;
use Bavix\Wallet\Internal\Events\WalletCreatedEventInterface;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Models\Wallet;

final class WalletCreatedEventAssembler implements WalletCreatedEventAssemblerInterface
{
    public function __construct(
        private ClockServiceInterface $clockService
    ) {
    }

    public function create(Wallet $wallet): WalletCreatedEventInterface
    {
        return new WalletCreatedEvent(
            $wallet->holder_type,
            $wallet->holder_id,
            $wallet->uuid,
            $wallet->getKey(),
            $this->clockService->now()
        );
    }
}
