<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\TransferCreatedEvent;
use Bavix\Wallet\Internal\Events\TransferCreatedEventInterface;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Models\Transfer;

final readonly class TransferCreatedEventAssembler implements TransferCreatedEventAssemblerInterface
{
    public function __construct(
        private ClockServiceInterface $clockService
    ) {
    }

    public function create(Transfer $transfer): TransferCreatedEventInterface
    {
        return new TransferCreatedEvent(
            $transfer->getKey(),
            $transfer->from_id,
            $transfer->to_id,
            $transfer->status,
            $this->clockService->now(),
        );
    }
}
