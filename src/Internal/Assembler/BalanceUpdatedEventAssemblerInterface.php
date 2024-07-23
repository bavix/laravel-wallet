<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Models\Wallet;

interface BalanceUpdatedEventAssemblerInterface
{
    /**
     * Create a balance updated event from a wallet.
     *
     * @param Wallet $wallet The wallet to create the event from.
     * @return BalanceUpdatedEventInterface The created event.
     */
    public function create(Wallet $wallet): BalanceUpdatedEventInterface;
}
