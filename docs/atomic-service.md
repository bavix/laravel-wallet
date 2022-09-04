## Atomic Service (Mutex in a db transaction)

Working with atomic wallet operations.

> You need wallet version 9.2 or higher

Before you start working with atomicity, you need to study the "Race Condition" section and configure the lock.

Sometimes it is necessary to apply actions to the user and the wallet atomically. For example, you want to raise an ad in the search and withdraw money from your wallet.
You need an Atomic Service Interface.

```php
use Bavix\Wallet\Services\AtomicServiceInterface;

app(AtomicServiceInterface::class)->block($wallet, function () use ($wallet, $entity) {
    $entity->increaseSales(); // update entity set sort_at=NOW() where id=123;
    $wallet->withdraw(100);
});
```

What's going on here?
We block the wallet and raise the ad in the transaction (yes, atomic immediately starts the transaction - this is the main difference from LockServiceInterface).
We raise the ad and deduct the amount from the wallet. If there are not enough funds to raise the ad, the error will complete the atomic operation and the transaction will roll back, and the lock on the wallet will be removed.

There is also an opportunity to block a lot of wallets. The operation is expensive, it generates N requests to the lock service. Maybe I'll optimize it in the future, but that's not for sure.

---

For example, we need to debit from two wallets at the same time. Then let's use the "blocks" method.

```php
use Bavix\Wallet\Services\AtomicServiceInterface;

app(AtomicServiceInterface::class)->blocks([$wallet1, $wallet2], function () use ($wallet1, $wallet2) {
    $wallet1->withdraw(100);
    $wallet2->withdraw(100);
});
```

In this case, we blocked both wallets and started the process of debiting funds. Debiting from both wallets will be considered a successful operation. If there are not enough funds on some wallet, the operation is canceled.

It worked! 
