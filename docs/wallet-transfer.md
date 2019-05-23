# Transfer between wallets

Transfer in our system are two well-known [Deposit](deposit) and [Withdraw](withdraw) 
operations that are performed in one transaction.

The transfer takes place between wallets.

---

- [User Model](#user-model)
- [Make a Transfer](#make-a-transfer)
- [Force Transfer](#force-transfer)

<a name="user-model"></a>
## User Model

Prepare the model, add the `HasWallet`, `HasWallets` trait's and `Wallet` interface.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet, HasWallets;
}
```

<a name="make-a-transfer"></a>
## Make a Transfer

Find user:

```php
$first = User::first(); 
$last = User::orderBy('id', 'desc')->first(); // last user
$first->getKey() !== $last->getKey(); // true
```

Create new wallets for users.
```php
$name = 'New Wallet';
$firstWallet = $first->createWallet(compact('name'));
$secondWallet = $second->createWallet(compact('name'));

$firstWallet->deposit(100);
$firstWallet->balance; // int(100)
$secondWallet->balance; // int(0)
```

The transfer will be from the first user to the second.

```php
$firstWallet->transfer($secondWallet, 5); 
$firstWallet->balance; // int(95)
$secondWallet->balance; // int(5)
```

It worked! 

<a name="force-transfer"></a>
## Force Transfer

Check the user's balance.

```php
$firstWallet->balance; // int(100)
$lastWallet->balance; // int(0)
```

The transfer will be from the first user to the second.

```php
$firstWallet->forceTransfer($secondWallet, 500); 
$firstWallet->balance; // int(-400)
$secondWallet->balance; // int(500)
```

It worked! 
