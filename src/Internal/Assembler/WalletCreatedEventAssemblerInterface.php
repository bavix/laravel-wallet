<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\WalletCreatedEventInterface;
use Bavix\Wallet\Models\Wallet;

interface WalletCreatedEventAssemblerInterface
{
    /**
     * Assemble the wallet created event.
     */
    public function create(Wallet $wallet): WalletCreatedEventInterface;
}
