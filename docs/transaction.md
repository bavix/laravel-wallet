## Transaction

> Starting from version 9.6 you can safely use standard framework transactions.

You definitely need to know the feature of transactions. The wallet is automatically blocked from the moment it is used until the end of the transaction. Therefore, it is necessary to use the wallet closer to the end of the transaction.

Very important! Almost all wallet transactions are blocking.

```php
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
$wallet->balanceInt; // now the wallet is blocked
doingMagic(); // running for a long time.
DB::commit(); // here will unlock the wallet
```

The point is that you need to minimize operations within transactions as much as possible. The longer the transaction, the longer the wallet lock.
The maximum wallet blocking time is set in the configuration. The longer the transaction takes, the more likely it is to get a race for the wallet.

---

If you are using a version below 9.6, then the following is relevant for you:
--

> Since version 9.2 it is safer to use AtomicServiceInterface.

> Is it possible to use laravel's inline transactions? No, It is Immpossible. This limitation is due to the internal architecture of the package. To achieve the maximum speed of work, work with an internal state of balance was needed. Starting with version 8.2, a special error has appeared that will inform you about incorrect work with the `TransactionStartException` package.

Sometimes you need to execute many simple queries. You want to keep the data atomic. To do this, you need `laravel-wallet` v7.1+.

It is necessary to write off the amount from the balance and raise the ad in the search. What happens if the service for raising an ad fails? We wrote off the money, but did not raise the ad. Received reputational losses. We can imagine the opposite situation, we first raise the ad in the search, but it does not work to write off the money. There are not enough funds. This functionality will help to solve all this. We monitor ONLY the state of the wallet, the rest falls on the developer. Let's take an unsuccessful lift, for example.

```php
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;

/** @var object $businessLogicService */
/** @var \Bavix\Wallet\Models\Wallet $payer */
$payer->balanceInt; // 9999
app(DatabaseServiceInterface::class)->transaction(static function () use ($payer) {
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
app(DatabaseServiceInterface::class)->transaction(static function () use ($payer) {
    $payer->withdraw(1000); // 8999
    $businessLogicService->doingMagic($payer); // successfully
}); // commit payer balance

$payer->balanceInt; // 8999
```

It worked! 
