<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\TransactionCreatedEvent;
use Bavix\Wallet\Internal\Events\TransactionCreatedEventInterface;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Models\Transaction;

final class TransactionCreatedEventAssembler implements TransactionCreatedEventAssemblerInterface
{
    public function __construct(
        private ClockServiceInterface $clockService
    ) {
    }

    public function create(Transaction $transaction): TransactionCreatedEventInterface
    {
        return new TransactionCreatedEvent(
            $transaction->getKey(),
            $transaction->type,
            $transaction->wallet_id,
            $this->clockService->now(),
        );
    }
}
