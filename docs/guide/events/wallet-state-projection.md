# Wallet State Projection <VersionTag version="v12.0.0" />

If you need custom wallet fields (`held_balance`, `balance_after`, `state_hash`),
use `WalletBatchProjectorInterface`.

Package provides hook only. Projection rules stay in your app code.

This keeps updates in same `UPDATE wallets ...` query.

## 1) Add columns

Add custom columns to `wallets` in your app migration.

## 2) Register projector

```php
'projectors' => [
    'wallet' => \App\Wallet\WalletStateBatchProjector::class,
],
```

## 3) Implement projector

```php
use Bavix\Wallet\Internal\Projector\WalletBatchProjectorInterface;

final readonly class WalletStateBatchProjector implements WalletBatchProjectorInterface
{
    public function project(array $balances, array $walletsById): array
    {
        $rows = [];
        foreach ($balances as $walletId => $resultingBalance) {
            $wallet = $walletsById[$walletId] ?? null;
            if ($wallet === null) {
                continue;
            }

            $heldBalance = (string) ($wallet->getAttribute('held_balance') ?? '0');
            $rows[$walletId] = [
                'balance_after' => $resultingBalance,
                'held_balance' => $heldBalance,
                'state_hash' => hash('sha256', $wallet->uuid.':'.$resultingBalance.':'.$heldBalance),
            ];
        }

        return $rows;
    }
}
```

For transaction columns, see [Transaction State Projection](/guide/events/transaction-state-projection).
