<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Listeners;

use Bavix\Wallet\Internal\Events\BalanceCommittingEventInterface;
use Bavix\Wallet\Test\Infra\PackageModels\WalletState;
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

        $walletSnapshots = $event->getWalletSnapshots();

        $projectionRows = [];
        foreach ($balances as $walletId => $resultingBalance) {
            $walletSnapshot = $walletSnapshots[$walletId] ?? null;
            if (! is_array($walletSnapshot)) {
                continue;
            }

            $uuid = $walletSnapshot['uuid'] ?? null;
            $attributes = $walletSnapshot['attributes'] ?? null;
            if (! is_string($uuid) || ! is_array($attributes)) {
                continue;
            }

            $heldBalance = (string) ($attributes['held_balance'] ?? '0');

            $projectionRows[] = [
                'id' => $walletId,
                'balance_after' => $resultingBalance,
                'held_balance' => $heldBalance,
                'state_hash' => hash('sha256', $uuid.':'.$resultingBalance.':'.$heldBalance),
            ];
        }

        if ($projectionRows === []) {
            return;
        }

        if (count($projectionRows) === 1) {
            $row = $projectionRows[0];

            WalletState::query()
                ->whereKey($row['id'])
                ->update([
                    'balance_after' => $row['balance_after'],
                    'held_balance' => $row['held_balance'],
                    'state_hash' => $row['state_hash'],
                ]);

            return;
        }

        $ids = [];
        foreach ($projectionRows as $row) {
            $ids[] = $row['id'];
        }

        WalletState::query()
            ->whereIn('id', $ids)
            ->update([
                'balance_after' => $this->buildCase($projectionRows, 'balance_after'),
                'held_balance' => $this->buildCase($projectionRows, 'held_balance'),
                'state_hash' => $this->buildCase($projectionRows, 'state_hash'),
            ]);
    }

    /**
     * @param list<array{id: int, balance_after: string, held_balance: string, state_hash: string}> $rows
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
