### API. Batch Transactions

> Since version 9.5+

Sometimes situations arise when there is a need to make multiple changes to wallets.
For example, we need to change the balance of many wallets at once. For example, the system administrator accrues a bonus for participating in some promotion. Previously, the code would look like this:
```php
use Bavix\Wallet\Services\AtomicServiceInterface;

app(AtomicServiceInterface::class)->blocks($wallets, function () use ($amount, $wallets) {
    foreach ($wallets as $wallet) {
        $wallet->deposit($amount);
    }
});
```

The code is working and everything works correctly. But what happens under the hood? Nothing good.
For 5 users it will look like this.
<img width="921" alt="exm1" src="https://user-images.githubusercontent.com/5111255/193401070-7956d8f7-1eed-458f-8c42-dde6b9ff51d4.png">

Since the operations inside an atomic operation can depend on each other, we will not be able to combine insert queries into a batch. But there is an opportunity to reduce the number of update queries and improve application performance out of the blue.

After small things, the situation will look like this.

<img width="915" alt="exm2" src="https://user-images.githubusercontent.com/5111255/193401407-b36ebe6f-d8f7-441d-a311-38300f4bf3cb.png">

As you can see, things are getting better. Still, I would like to be able to tell the package that the changes are independent of each other. In this case, the package will be able to collapse all insert queries into a single query and insert in a batch.

Here new api handles can help us:
```php
// For multiple transactions.
interface TransactionQueryHandlerInterface
{
    /**
     * @param non-empty-array<TransactionQuery> $objects
     * @return non-empty-array<string, Transaction>
     * @throws ExceptionInterface
     */
    public function apply(array $objects): array;
}

// For multiple transfers of funds.
interface TransferQueryHandlerInterface
{
    /**
     * @param non-empty-array<TransferQuery> $objects
     * @return non-empty-array<string, Transfer>
     * @throws ExceptionInterface
     */
    public function apply(array $objects): array;
}
```

Let's use the API handle.
```php
use Bavix\Wallet\External\Api\TransactionQuery;
use Bavix\Wallet\External\Api\TransactionQueryHandlerInterface;

app(TransactionQueryHandlerInterface::class)->apply(
    array_map(
        static fn (Wallet $wallet) => TransactionQuery::createDeposit($wallet, $amount, null),
        $wallets
     )
);
```

And now look at the result and it is impressive.
<img width="920" alt="exm3" src="https://user-images.githubusercontent.com/5111255/193401971-b94775bb-c33a-47f0-a07a-f5757b71a153.png">

But it is worth noting that these are highly efficient api handles and they do not check the balance of the wallet before making changes. If you need it, then you have to do something like this.

```php
use Bavix\Wallet\External\Api\TransactionQuery;
use Bavix\Wallet\External\Api\TransactionQueryHandlerInterface;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\ConsistencyServiceInterface;

app(AtomicServiceInterface::class)->blocks($wallets, function () use ($wallets, $amount) {
    foreach ($wallets as $wallet) {
        app(ConsistencyServiceInterface::class)->checkPotential($wallet, $amount);

    }

    app(TransactionQueryHandlerInterface::class)->apply(
        array_map(
            static fn (Wallet $wallet) => TransactionQuery::createWithdraw($wallet, $amount, null),
            $wallets
        )
    );
});
```

---
It worked! 

