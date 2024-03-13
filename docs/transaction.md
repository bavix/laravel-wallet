## Transaction

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
It's simple!
