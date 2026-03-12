<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Listeners;

use Bavix\Wallet\Internal\Events\TransactionCommittingEventInterface;
use Bavix\Wallet\Test\Infra\PackageModels\Transaction;
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
        $finalBalanceCases = [];
        $checksumCases = [];
        $pdo = DB::getPdo();

        foreach ($rows as $row) {
            $id = $row['id'];
            $ids[] = $id;

            $finalBalanceCases[] = 'WHEN '.$id.' THEN '.$pdo->quote($row['final_balance']);
            $checksumCases[] = 'WHEN '.$id.' THEN '.$pdo->quote($row['checksum']);
        }

        DB::table((new Transaction())->getTable())
            ->whereIn('id', $ids)
            ->update([
                'final_balance' => DB::raw('CASE id '.implode(' ', $finalBalanceCases).' END'),
                'checksum' => DB::raw('CASE id '.implode(' ', $checksumCases).' END'),
            ]);
    }
}
