<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\TransactionCreatedEventInterface;
use Bavix\Wallet\Models\Transaction;

interface TransactionCreatedEventAssemblerInterface
{
    /**
     * Creates a new instance of the TransactionCreatedEventInterface from the given Transaction model.
     *
     * @param Transaction $transaction The transaction model to create the event from.
     * @return TransactionCreatedEventInterface The created event.
     */
    public function create(Transaction $transaction): TransactionCreatedEventInterface;
}
