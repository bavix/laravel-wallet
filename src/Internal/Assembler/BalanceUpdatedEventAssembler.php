<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\BalanceUpdatedEvent;
use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Models\Wallet;

final class BalanceUpdatedEventAssembler implements BalanceUpdatedEventAssemblerInterface
{
    public function create(Wallet $wallet, string $balance): BalanceUpdatedEventInterface
    {
        return new BalanceUpdatedEvent((int) $wallet->getKey(), $wallet->uuid, $balance);
    }
}
