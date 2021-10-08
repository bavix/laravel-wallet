# To refresh the balance

There are situations when you create a lot of unconfirmed operations, 
and then abruptly confirm everything. 
In this case, the user's balance will not change. 
You must be forced to refresh the balance.

---

## User Model

[User Simple](_include/models/user_simple.md ':include')

## Get the current balance for your wallet

Let's say the user's balance

```php
$user->id; // 5
$user->balance; // 27
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
$user->balance; // 27
$user->wallet->refreshBalance();
$user->balance; // 42
```

It worked! 
