# Tracking balance changes <VersionTag version="v7.2.0" />

There are tasks when you urgently need to do something when the user's balance changes. A frequent case of transferring data via websockets to the front-end.

Since v7.2.0, the package provides an interface you can subscribe to.
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

## Need batch wallet-state projection?

Use the dedicated guide: [Wallet State Projection](/guide/events/wallet-state-projection).
