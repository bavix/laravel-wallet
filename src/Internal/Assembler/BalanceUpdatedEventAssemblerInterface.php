<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Models\Wallet;

interface BalanceUpdatedEventAssemblerInterface
{
    public function create(Wallet $wallet): BalanceUpdatedEventInterface;
}
