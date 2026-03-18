# Transaction state projection

Issue #1015 asks for `final_balance` and `checksum` in the `transactions` table.

You can implement this as an extension, without changing package internals:

1. add custom columns to your `transactions` table,
2. subscribe to `TransactionCommittingEventInterface`,
3. update transaction state in batch, using runtime payload.

```php
use App\Models\Transaction;
use Bavix\Wallet\Internal\Events\TransactionCommittingEventInterface;
use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Support\Facades\DB;

final class TransactionStateProjectorListener
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
```

For `frozen_balance` on wallets, use [Wallet State Projection](/guide/events/wallet-state-projection).
