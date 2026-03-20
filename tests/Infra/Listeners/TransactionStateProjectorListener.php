<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Listeners;

use Bavix\Wallet\Internal\Events\TransactionCommittingEventInterface;
use Bavix\Wallet\Test\Infra\PackageModels\TransactionState;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

final readonly class TransactionStateProjectorListener
{
    public function handle(TransactionCommittingEventInterface $event): void
    {
        $transactions = $event->getTransactions();
        $resultingBalances = $event->getResultingBalances();

        $projectionRows = [];
        foreach ($transactions as $transaction) {
            $transactionId = $transaction['id'] ?? null;
            $transactionAmount = $transaction['amount'] ?? null;
            if (! is_int($transactionId)) {
                continue;
            }
            if (! is_string($transactionAmount)) {
                continue;
            }

            $resultingBalance = $resultingBalances[$transactionId] ?? null;
            if ($resultingBalance === null) {
                continue;
            }

            $projectionRows[] = [
                'id' => $transactionId,
                'balance_after' => $resultingBalance,
                'state_hash' => hash('sha256', $transactionId.':'.$transactionAmount.':'.$resultingBalance),
            ];
        }

        if ($projectionRows === []) {
            return;
        }

        if (count($projectionRows) === 1) {
            $row = $projectionRows[0];

            TransactionState::query()
                ->whereKey($row['id'])
                ->update([
                    'balance_after' => $row['balance_after'],
                    'state_hash' => $row['state_hash'],
                ]);

            return;
        }

        $ids = [];
        foreach ($projectionRows as $row) {
            $ids[] = $row['id'];
        }

        TransactionState::query()
            ->whereIn('id', $ids)
            ->update([
                'balance_after' => $this->buildCase($projectionRows, 'balance_after'),
                'state_hash' => $this->buildCase($projectionRows, 'state_hash'),
            ]);
    }

    /**
     * @param list<array{id: int, balance_after: string, state_hash: string}> $rows
     */
    private function buildCase(array $rows, string $column): Expression
    {
        $pdo = DB::getPdo();
        $cases = [];
        foreach ($rows as $row) {
            $cases[] = 'WHEN '.$row['id'].' THEN '.$pdo->quote((string) $row[$column]);
        }

        return DB::raw('CASE id '.implode(' ', $cases).' END');
    }
}
