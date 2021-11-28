## Transaction

Sometimes you need to execute many simple queries. You want to keep the data atomic. To do this, you need `laravel-wallet` v7.1+.

It is necessary to write off the amount from the balance and raise the ad in the search. What happens if the service for raising an ad fails? We wrote off the money, but did not raise the ad. Received reputational losses. We can imagine the opposite situation, we first raise the ad in the search, but it does not work to write off the money. There are not enough funds. This functionality will help to solve all this. We monitor ONLY the state of the wallet, the rest falls on the developer. Let's take an unsuccessful lift, for example.

```php
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;

/** @var object $businessLogicService */
/** @var \Bavix\Wallet\Models\Wallet $payer */
$payer->balanceInt; // 9999
app(DatabaseServiceInterface::class)->transaction(statuc function () use ($payer) {
    $payer->withdraw(1000); // 8999
    $businessLogicService->doingMagic($payer); // throws an exception
}); // rollback payer balance

$payer->balanceInt; // 9999
```

Now let's look at the successful raising of the ad.

```php
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;

/** @var object $businessLogicService */
/** @var \Bavix\Wallet\Models\Wallet $payer */
$payer->balanceInt; // 9999
app(DatabaseServiceInterface::class)->transaction(statuc function () use ($payer) {
    $payer->withdraw(1000); // 8999
    $businessLogicService->doingMagic($payer); // successfully
}); // commit payer balance

$payer->balanceInt; // 8999
```

It worked! 
