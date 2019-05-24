# To refresh the balance

There are situations when you create a lot of unconfirmed operations, 
and then abruptly confirm everything. 
In this case, the user's balance will not change. 
You must be forced to refresh the balance.

---

## User Model

Prepare the model, add the `HasWallet` trait and `Wallet` interface.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet;
}
```

## Get the current balance for your wallet

Let's say the user's balance

```php
$user->id; // int(5)
$user->balance; // int(27)
```

And he has unconfirmed transactions.
Confirm all transactions.

```sql
update transactions 
set confirmed=1 
where confirmed=0 and 
      payable_type='App\Models\User' and 
      payable_id=5;
-- 212 rows affected in 54 ms
```

Refresh the balance.

```php
$user->balance; // int(27)
$user->wallet->refreshBalance();
$user->balance; // int(42)
```

It worked! 
