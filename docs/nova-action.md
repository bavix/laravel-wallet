## Nova Action

> Use only if you have a package version below 9.6

As you know, the package works with internal state. You can read more [here](https://github.com/bavix/laravel-wallet/pull/412) and [here](https://github.com/bavix/laravel-wallet/issues/455).

The action runs inside a transaction, which means you need to reset the transaction manually.

```php
use Illuminate\Support\Facades\DB;

public function handle(ActionFields $fields, Collection $models)
{
    DB::rollBack(0);
    ...
}
```

Yes, it may not be convenient for someone, but you have to measure it. At the moment, there is no other solution.

But what if you want to use a transaction?
Use according to [documentation](transaction).

Why was the decision made to move away from embedded transactions?
The problem with embedded transactions is that the package changes the state of not only the database, but also the cache systems. Inside the transaction, sagas are implemented that update the balance in the cache systems of an already successful update inside the database.
This feature was well described by me in the [pull request](https://github.com/bavix/laravel-wallet/pull/412).

It worked! 
