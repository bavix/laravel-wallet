## User Model

Add the `CanConfirm` trait and `Confirmable` interface to your User model.

```php
use Bavix\Wallet\Interfaces\Confirmable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\CanConfirm;
use Bavix\Wallet\Traits\HasWallet;

class UserConfirm extends Model implements Wallet, Confirmable
{
    use HasWallet, CanConfirm;
}
```

### Example:

Sometimes you need to create an operation and confirm its field. 
That is what this trey does.

```php
$user->balance; // int(0)
$transaction = $user->deposit(100, null, false); // not confirm
$transaction->confirmed; // bool(false)
$user->balance; // int(0)

$user->confirm($transaction); // bool(true)
$transaction->confirmed; // bool(true)

$user->balance; // int(100) 
```

It worked! 
