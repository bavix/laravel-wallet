# Tracking balance changes

There are tasks when you urgently need to do something when the user's balance changes. A frequent case of transferring data via websockets to the front-end.

Version 7.2 introduces an interface to which you can subscribe.
This is done using standard Laravel methods.
More information in the [documentation](https://laravel.com/docs/8.x/events).

```php
use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;

protected $listen = [
    BalanceUpdatedEventInterface::class => [
        MyBalanceUpdatedListener::class,
    ],
];
```

And then we create a listener.

```php
use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;

class MyBalanceUpdatedListener
{
    public function handle(BalanceUpdatedEventInterface $event): void
    {
        // And then the implementation...
    }
}
```

It's simple!

## Custom wallet state (frozen/final/checksum)

If you need custom wallet state fields (for example `frozen_balance`, `final_balance`, `checksum`),
you can implement it in your app without changing package internals.

1. Add columns in your app migration.
2. Subscribe to Laravel `TransactionCommitting` event.
3. Project derived fields in a listener.

```php
use Illuminate\Database\Events\TransactionCommitting;
use Illuminate\Support\Facades\DB;
use App\Models\Wallet;

final class WalletStateProjectorListener
{
    public function handle(TransactionCommitting $event): void
    {
        if (DB::connection($event->connectionName)->transactionLevel() !== 1) {
            return;
        }

        Wallet::query()->chunkById(100, static function ($wallets): void {
            foreach ($wallets as $wallet) {
                $finalBalance = $wallet->balance;
                $frozenBalance = (string) ($wallet->frozen_balance ?? '0');

                $wallet->forceFill([
                    'final_balance' => $finalBalance,
                    'frozen_balance' => $frozenBalance,
                    'checksum' => hash('sha256', $wallet->uuid.':'.$finalBalance.':'.$frozenBalance),
                ])->saveQuietly();
            }
        });
    }
}
```

This pattern is covered by package tests (`WalletExtensionTest`) and works for any wallet-changing flow that runs through package transactions.
