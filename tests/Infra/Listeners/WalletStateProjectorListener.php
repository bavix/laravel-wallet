<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Listeners;

use Bavix\Wallet\Internal\Events\BalanceCommittingEventInterface;
use Bavix\Wallet\Test\Infra\PackageModels\Wallet as WalletModel;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

final readonly class WalletStateProjectorListener
{
    public function handle(BalanceCommittingEventInterface $event): void
    {
        $balances = $event->getBalances();
        if ($balances === []) {
            return;
        }

        $walletStates = $event->getWalletStates();

        $rows = [];
        foreach ($balances as $walletId => $finalBalance) {
            $walletState = $walletStates[$walletId] ?? null;
            if (! is_array($walletState)) {
                continue;
            }

            $rows[] = [
                'id' => $walletId,
                'final_balance' => $finalBalance,
                'frozen_balance' => $walletState['frozen_balance'],
                'checksum' => hash('sha256', $walletState['uuid'].':'.$finalBalance.':'.$walletState['frozen_balance']),
            ];
        }

        if ($rows === []) {
            return;
        }

        if (count($rows) === 1) {
            $row = $rows[0];

            WalletModel::query()
                ->whereKey($row['id'])
                ->update([
                    'final_balance' => $row['final_balance'],
                    'frozen_balance' => $row['frozen_balance'],
                    'checksum' => $row['checksum'],
                ]);

            return;
        }

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = $row['id'];
        }

        WalletModel::query()
            ->whereIn('id', $ids)
            ->update([
                'final_balance' => $this->buildCase($rows, 'final_balance'),
                'frozen_balance' => $this->buildCase($rows, 'frozen_balance'),
                'checksum' => $this->buildCase($rows, 'checksum'),
            ]);
    }

    /**
     * @param list<array{id: int, final_balance: string, frozen_balance: string, checksum: string}> $rows
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
