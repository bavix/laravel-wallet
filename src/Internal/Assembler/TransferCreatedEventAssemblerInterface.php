<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\TransferCreatedEventInterface;
use Bavix\Wallet\Models\Transfer;

interface TransferCreatedEventAssemblerInterface
{
    public function create(Transfer $transfer): TransferCreatedEventInterface;
}
