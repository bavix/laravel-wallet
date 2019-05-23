# To recalculate the balance

There are situations when you create a lot of unconfirmed operations, 
and then abruptly confirm everything. 
In this case, the user's balance will not change. 
You must be forced to recalculate the balance.

---

- [User Model](#user-model)
- [Recalculate](#recalculate)

<a name="user-model"></a>
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

<a name="recalculate"></a>
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

Recalculate the balance.

```php
$user->balance; // int(27)
$user->wallet->calculateBalance();
$user->balance; // int(42)
```

It worked! 
