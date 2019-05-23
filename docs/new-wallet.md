# Create a wallet and use it

Virtual wallets can be any number. 
The main thing that they did not match the `slug`.

---

- [User Model](#user-model)
- [Create a wallet](#create-wallet)
- [Get wallet](#find-wallet)
- [Get default wallet](#default-wallet)

<a name="user-model"></a>
## User Model

Add the `HasWallet`, `HasWallets` trait's and `Wallet` interface to model.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet, HasWallets;
}
```

<a name="create-wallet"></a>
## Create a wallet

Find user:

```php
$user = User::first(); 
```

As the user uses `HasWallet`, he will have `balance` property. 
Check the user's balance.

```php
$user->balance; // int(0)
```

It is the balance of the wallet by default.
Create a new wallet.

```php
$wallet = $user->createWallet([
    'name' => 'New Wallet',
    'slug' => 'my-wallet',
]);

$wallet->deposit(100);
$wallet->balance; // int(100)

$user->deposit(10); 
$user->balance; // int(10)
```

<a name="find-wallet"></a>
## How to get the right wallet?

```php
$myWallet = $user->getWallet('my-wallet');
$myWallet->balance; // int(100)
```

<a name="default-wallet"></a>
## How to get the default wallet?

```php
$wallet = $user->wallet;
$wallet->balance; // int(10)
```

It worked! 
