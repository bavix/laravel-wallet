### API. Batch Transfers

> Since version 9.5+

If you need multiple transfers between wallets, you can use a high-performance handle. It is worth remembering that the pen does not check the balance of the wallet before transferring, you need to take care of this yourself.

Previously, you would have written the following code:
```php
use Bavix\Wallet\Services\AtomicServiceInterface;

app(AtomicServiceInterface::class)->block($from, function () use ($amount, $from, $wallets) {
    foreach ($wallets as $wallet) {
        $from->forceTransfer($wallet, $amount);
    }
});
```

This would lead to the generation of a huge number of requests to the database and cache, because. the package does not know that the response from `forceTransfer` is not used by you at all inside AtomicService. Now, you can report it:
```php
use Bavix\Wallet\External\Api\TransferQuery;
use Bavix\Wallet\External\Api\TransferQueryHandlerInterface;

app(TransferQueryHandlerInterface::class)->apply(
    array_map(
        static fn (Wallet $wallet) => new TransferQuery($from, $wallet, $amount, null),
        $wallets
     )
);
```

The package will optimize queries and execute them in a single transaction. I strongly advise against creating large packs, because. this can lead to a large increase in request queuing.

---

In version 10.x, it became possible to create transactions&transfers with a given uuid (generate on the client side).
The main thing is to keep uniqueness.

```php
use Bavix\Wallet\External\Api\TransferQuery;

new TransferQuery($from, $wallet, $amount, new \Bavix\Wallet\External\Dto\Extra(
    deposit: new \Bavix\Wallet\External\Dto\Option(
        null,
        uuid: '71cecafe-da10-464f-9e00-c80437bb4c3e', // deposit transaction
    ),
    withdraw: new \Bavix\Wallet\External\Dto\Option(
        null,
        uuid: '3805730b-39a1-419d-8715-0b7cc3f1ffc2', // withdraw transaction
    ),
    uuid: 'f8becf81-3993-43d7-81f1-7a725c72e976', // transfer uuid
))
```

---
It worked! 

