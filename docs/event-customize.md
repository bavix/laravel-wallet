## Customizing events

Sometimes you want to modify the standard events of a package. This is done quite simply.

Let's add broadcast support? We need to implement our event from the interface.

```php
use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

final class MyUpdatedEvent implements BalanceUpdatedEventInterface, ShouldBroadcast
{
    public function __construct(
        private \Bavix\Wallet\Models\Wallet $wallet,
        private DateTimeImmutable $updatedAt,
    ) {}
    
    public function getWalletId(): int { return $this->wallet->getKey(); }
    public function getWalletUuid(): string { return $this->wallet->uuid; }
    public function getBalance(): string { return $this->wallet->balanceInt; }
    public function getUpdatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function broadcastOn(): array
    {
        return $this->wallet->getAttributes();
    }
}
```

The event is ready, but that's not all. Now you need to implement your assembler class, which will create an event inside the package.

```php
use Bavix\Wallet\Internal\Assembler\BalanceUpdatedEventAssemblerInterface;

class MyUpdatedEventAssembler implements BalanceUpdatedEventAssemblerInterface
{
    public function create(\Bavix\Wallet\Models\Wallet $wallet) : \Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface
    {
        return new MyUpdatedEvent($wallet, new DateTimeImmutable());
    }
}
```

Next, go to the package settings (wallet.php).
We change the event to a new one.

```php
    'assemblers' => [
        'balance_updated_event' => MyUpdatedEventAssembler::class,
    ],
```

Then everything is the same as with the standard events of the package.

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

It worked! 
