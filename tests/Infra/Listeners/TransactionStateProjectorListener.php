<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Listeners;

use Bavix\Wallet\Internal\Events\TransactionCommittingEventInterface;
use Bavix\Wallet\Test\Infra\PackageModels\Transaction;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

final readonly class TransactionStateProjectorListener
{
    public function handle(TransactionCommittingEventInterface $event): void
    {
        $transactions = $event->getTransactions();
        $finalBalances = $event->getFinalBalances();

        $rows = [];
        foreach ($transactions as $transaction) {
            $finalBalance = $finalBalances[$transaction['id']] ?? null;
            if ($finalBalance === null) {
                continue;
            }

            $rows[] = [
                'id' => $transaction['id'],
                'final_balance' => $finalBalance,
                'checksum' => hash('sha256', $transaction['id'].':'.$transaction['amount'].':'.$finalBalance),
            ];
        }

        if ($rows === []) {
            return;
        }

        if (count($rows) === 1) {
            $row = $rows[0];

            Transaction::query()
                ->whereKey($row['id'])
                ->update([
                    'final_balance' => $row['final_balance'],
                    'checksum' => $row['checksum'],
                ]);

            return;
        }

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = $row['id'];
        }

        Transaction::query()
            ->whereIn('id', $ids)
            ->update([
                'final_balance' => $this->buildCase($rows, 'final_balance'),
                'checksum' => $this->buildCase($rows, 'checksum'),
            ]);
    }

    /**
     * @param list<array{id: int, final_balance: string, checksum: string}> $rows
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
