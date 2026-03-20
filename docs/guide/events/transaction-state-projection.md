# Transaction state projection

Issue #1015 asks for `balance_after` and `state_hash` in the `transactions` table.

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
        $resultingBalances = $event->getResultingBalances();

        $rows = [];
        foreach ($transactions as $transaction) {
            $transactionId = $transaction['id'] ?? null;
            $transactionAmount = $transaction['amount'] ?? null;

            if (! is_int($transactionId) || ! is_string($transactionAmount)) {
                continue;
            }

            $resultingBalance = $resultingBalances[$transactionId] ?? null;
            if ($resultingBalance === null) {
                continue;
            }

            $rows[] = [
                'id' => $transactionId,
                'balance_after' => $resultingBalance,
                'state_hash' => hash('sha256', $transactionId.':'.$transactionAmount.':'.$resultingBalance),
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
                    'balance_after' => $row['balance_after'],
                    'state_hash' => $row['state_hash'],
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
                'balance_after' => $this->buildCase($rows, 'balance_after'),
                'state_hash' => $this->buildCase($rows, 'state_hash'),
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
```

For `held_balance` on wallets, use [Wallet State Projection](/guide/events/wallet-state-projection).
