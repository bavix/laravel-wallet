# Cancel Transaction

Sometimes you need to cancel a confirmed transaction. For example, money was received or debited by mistake. You can reset the confirmation of a specific transaction.

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

> You can only cancel the transaction with the wallet you paid with.

## To cancel

### Example:

Created a transaction, and after resetting its confirmation.

```php
$user->balance; // 0
$transaction = $user->deposit(100); // confirmed transaction 
$transaction->confirmed; // bool(true)
$user->balance; // 100

$user->resetConfirm($transaction); // bool(true)
$transaction->confirmed; // bool(false)

$user->balance; // 0 
```

It's simple!
