# Tracking the creation of wallet transactions <VersionTag version="v9.1.0" />

The events are similar to the events for updating the balance, only for the creation of a wallet. A frequent case of transferring data via websockets to the front-end.

Since v9.1.0, the package provides an interface you can subscribe to.
This is done using standard Laravel methods.
More information in the [documentation](https://laravel.com/docs/8.x/events).

```php
use Bavix\Wallet\Internal\Events\TransactionCreatedEventInterface;

protected $listen = [
    TransactionCreatedEventInterface::class => [
        MyWalletTransactionCreatedListener::class,
    ],
];
```

And then we create a listener.

```php
use Bavix\Wallet\Internal\Events\TransactionCreatedEventInterface;

class MyWalletTransactionCreatedListener
{
    public function handle(TransactionCreatedEventInterface $event): void
    {
        // And then the implementation...
    }
}
```

It's simple!
