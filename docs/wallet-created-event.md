## Tracking the creation of wallets

The events are similar to the events for updating the balance, only for the creation of a wallet. A frequent case of transferring data via websockets to the front-end.

Version 7.3 introduces an interface to which you can subscribe.
This is done using standard Laravel methods.
More information in the [documentation](https://laravel.com/docs/8.x/events).

```php
use Bavix\Wallet\Internal\Events\WalletCreatedEventInterface;

protected $listen = [
    WalletCreatedEventInterface::class => [
        MyWalletCreatedListener::class,
    ],
];
```

And then we create a listener.

```php
use Bavix\Wallet\Internal\Events\WalletCreatedEventInterface;

class MyWalletCreatedListener
{
    public function handle(WalletCreatedEventInterface $event): void
    {
        // And then the implementation...
    }
}
```

It worked! 
