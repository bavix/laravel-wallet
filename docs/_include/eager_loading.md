## Eager Loading

When accessing Eloquent relationships as properties, the relationship data is "lazy loaded". 
This means the relationship data is not actually loaded until you first access the property. However, Eloquent can "eager load" relationships at the time you query the parent model. Eager loading alleviates the N + 1 query problem. To illustrate the N + 1 query problem, consider a `Wallet` model that is related to `User`:

Add the `HasWallet` trait and `Wallet` interface to model.
```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet; // public function wallet(): MorphOne...
}
```

Now, let's retrieve all wallets and their users:

```php
$users = User::all();

foreach ($users as $user) {
    // echo $user->wallet->balance;
    echo $user->balance; // Abbreviated notation
}
```

This loop will execute 1 query to retrieve all of the users on the table, then another query for each user to retrieve the wallet. So, if we have 25 users, the code above would run 26 queries: 1 for the original user, and 25 additional queries to retrieve the wallet of each user.

Thankfully, we can use eager loading to reduce this operation to just 2 queries. When querying, you may specify which relationships should be eager loaded using the with method:

```php
$users = User::with('wallet')->all();

foreach ($users as $user) {
    // echo $user->wallet->balance;
    echo $user->balance; // Abbreviated notation
}
```

For this operation, only two queries will be executed.
