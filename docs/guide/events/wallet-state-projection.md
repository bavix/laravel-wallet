# Wallet state projection

If you need custom wallet state fields (for example `frozen_balance`, `final_balance`, `checksum`),
you can implement them in your app without changing package internals.

If you need `final_balance` and `checksum` on transactions (issue #1015),
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

            Wallet::query()
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

        Wallet::query()
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
```

This pattern is covered by package tests (`WalletExtensionTest`) and works for all wallet-changing flows.
