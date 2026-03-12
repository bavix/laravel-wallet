<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\TransactionCommittingEventInterface;
use Bavix\Wallet\Models\Transaction;

interface TransactionCommittingEventAssemblerInterface
{
    /**
     * @param array<string, Transaction> $transactions
     * @param array<int, string> $finalBalances
     */
    public function create(array $transactions, array $finalBalances): TransactionCommittingEventInterface;
}
