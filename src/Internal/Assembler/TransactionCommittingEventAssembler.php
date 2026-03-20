<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Assembler;

use Bavix\Wallet\Internal\Events\TransactionCommittingEvent;
use Bavix\Wallet\Internal\Events\TransactionCommittingEventInterface;

final readonly class TransactionCommittingEventAssembler implements TransactionCommittingEventAssemblerInterface
{
    public function create(array $transactions, array $resultingBalances): TransactionCommittingEventInterface
    {
        $items = [];
        foreach ($transactions as $uuid => $transaction) {
            $items[$uuid] = [
                'id' => $transaction->getKey(),
                'amount' => (string) $transaction->amount,
            ];
        }

        return new TransactionCommittingEvent($items, $resultingBalances);
    }
}
