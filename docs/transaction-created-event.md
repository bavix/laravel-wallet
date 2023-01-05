## Tracking the creation of wallet transactions

The events are similar to the events for updating the balance, only for the creation of a wallet. A frequent case of transferring data via websockets to the front-end.

Version 9.1 introduces an interface to which you can subscribe.
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

It worked! 
