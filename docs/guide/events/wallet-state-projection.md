# Wallet state projection

If you need custom wallet state fields (for example `held_balance`, `balance_after`, `state_hash`),
you can implement them in your app without changing package internals.

If you need `balance_after` and `state_hash` on transactions (issue #1015),
use [Transaction State Projection](/guide/events/transaction-state-projection).

Use `BalanceCommittingEventInterface` for batch-safe projection inside a commit cycle.

## 1) Add custom columns

Create a migration in your app and add columns to the wallet table.

## 2) Subscribe to the committing event

```php
use Bavix\Wallet\Internal\Events\BalanceCommittingEventInterface;

protected $listen = [
    BalanceCommittingEventInterface::class => [
        WalletStateProjectorListener::class,
    ],
];
```

## 3) Project data in batch

```php
use App\Models\Wallet;
use Bavix\Wallet\Internal\Events\BalanceCommittingEventInterface;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

final class WalletStateProjectorListener
{
    public function handle(BalanceCommittingEventInterface $event): void
    {
        $balances = $event->getBalances();
        if ($balances === []) {
            return;
        }

        $walletSnapshots = $event->getWalletSnapshots();

        $rows = [];
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

            $rows[] = [
                'id' => $walletId,
                'balance_after' => $resultingBalance,
                'held_balance' => $heldBalance,
                'state_hash' => hash('sha256', $uuid.':'.$resultingBalance.':'.$heldBalance),
            ];
        }

        if ($rows === []) {
            return;
        }

        if (count($rows) === 1) {
            $row = $rows[0];

            Wallet::query()
                ->whereKey($row['id'])
                ->update([
                    'balance_after' => $row['balance_after'],
                    'held_balance' => $row['held_balance'],
                    'state_hash' => $row['state_hash'],
                ]);

            return;
        }

        $ids = [];
        foreach ($rows as $row) {
            $ids[] = $row['id'];
        }

        Wallet::query()
            ->whereIn('id', $ids)
            ->update([
                'balance_after' => $this->buildCase($rows, 'balance_after'),
                'held_balance' => $this->buildCase($rows, 'held_balance'),
                'state_hash' => $this->buildCase($rows, 'state_hash'),
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
```

This pattern is covered by package tests (`WalletExtensionTest`) and works for all wallet-changing flows.
