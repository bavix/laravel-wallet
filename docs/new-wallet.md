# Create a wallet and use it

Virtual wallets can be any number. 
The main thing that they did not match the `slug`.

---

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
$user->hasWallet('my-wallet'); // bool(false)
$wallet = $user->createWallet([
    'name' => 'New Wallet',
    'slug' => 'my-wallet',
]);

$user->hasWallet('my-wallet'); // bool(true)

$wallet->deposit(100);
$wallet->balance; // int(100)

$user->deposit(10); 
$user->balance; // int(10)
```

## How to get the right wallet?

```php
$myWallet = $user->getWallet('my-wallet');
$myWallet->balance; // int(100)
```

## How to get the default wallet?

```php
$wallet = $user->wallet;
$wallet->balance; // int(10)
```

It worked! 
