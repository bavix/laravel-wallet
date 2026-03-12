# Transaction state projection

Issue #1015 asks for `final_balance` and `checksum` in the `transactions` table.

You can implement this as an extension, without changing package internals:

1. add custom columns to your `transactions` table,
2. subscribe to `TransactionCommittingEventInterface`,
3. update transaction state in batch, using runtime payload.

```php
use App\Models\Transaction;
use Bavix\Wallet\Internal\Events\TransactionCommittingEventInterface;
use Illuminate\Support\Facades\DB;

final class TransactionStateProjectorListener
{
    public function handle(TransactionCommittingEventInterface $event): void
    {
        $rows = [];
        $finalBalances = $event->getFinalBalances();

        foreach ($event->getTransactions() as $transaction) {
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

        $ids = [];
        $finalCases = [];
        $checksumCases = [];
        $pdo = DB::getPdo();

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $ids[] = $id;
            $finalCases[] = 'WHEN '.$id.' THEN '.$pdo->quote($row['final_balance']);
            $checksumCases[] = 'WHEN '.$id.' THEN '.$pdo->quote($row['checksum']);
        }

        DB::table((new Transaction())->getTable())
            ->whereIn('id', $ids)
            ->update([
                'final_balance' => DB::raw('CASE id '.implode(' ', $finalCases).' END'),
                'checksum' => DB::raw('CASE id '.implode(' ', $checksumCases).' END'),
            ]);
    }
}
```

For `frozen_balance` on wallets, use [Wallet State Projection](/guide/events/wallet-state-projection).
