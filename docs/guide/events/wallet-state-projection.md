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
use Illuminate\Support\Facades\DB;

final class WalletStateProjectorListener
{
    public function handle(BalanceCommittingEventInterface $event): void
    {
        $rows = [];
        $walletStates = $event->getWalletStates();

        foreach ($event->getBalances() as $walletId => $finalBalance) {
            $walletState = $walletStates[$walletId] ?? null;
            if (! is_array($walletState)) {
                continue;
            }

            $frozenBalance = $walletState['frozen_balance'];

            $rows[] = [
                'id' => $walletId,
                'final_balance' => $finalBalance,
                'frozen_balance' => $frozenBalance,
                'checksum' => hash('sha256', $walletState['uuid'].':'.$finalBalance.':'.$frozenBalance),
            ];
        }

        $ids = [];
        $finalCases = [];
        $frozenCases = [];
        $checksumCases = [];
        $pdo = DB::getPdo();

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $ids[] = $id;
            $finalCases[] = 'WHEN '.$id.' THEN '.$pdo->quote($row['final_balance']);
            $frozenCases[] = 'WHEN '.$id.' THEN '.$pdo->quote($row['frozen_balance']);
            $checksumCases[] = 'WHEN '.$id.' THEN '.$pdo->quote($row['checksum']);
        }

        DB::table((new Wallet())->getTable())
            ->whereIn('id', $ids)
            ->update([
                'final_balance' => DB::raw('CASE id '.implode(' ', $finalCases).' END'),
                'frozen_balance' => DB::raw('CASE id '.implode(' ', $frozenCases).' END'),
                'checksum' => DB::raw('CASE id '.implode(' ', $checksumCases).' END'),
            ]);
    }
}
```

This pattern is covered by package tests (`WalletExtensionTest`) and works for all wallet-changing flows.
