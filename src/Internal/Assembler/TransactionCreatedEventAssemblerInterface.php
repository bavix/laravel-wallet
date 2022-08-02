<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\TransactionCreatedEventInterface;
use Bavix\Wallet\Models\Transaction;

interface TransactionCreatedEventAssemblerInterface
{
    public function create(Transaction $transaction): TransactionCreatedEventInterface;
}
