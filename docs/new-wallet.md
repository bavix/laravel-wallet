# Create a wallet and use it

You can create an unlimited number of wallets, but the `slug` for each wallet should be unique.

---

## User Model

Add the `HasWallets` trait's and `Wallet` interface to model.

```php
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallets;
}
```

## Create a wallet

Find user:

```php
$user = User::first(); 
```

Create a new wallet.

```php
$user->hasWallet('my-wallet'); // bool(false)
$wallet = $user->createWallet([
    'name' => 'New Wallet',
    'slug' => 'my-wallet',
]);

$user->hasWallet('my-wallet'); // bool(true)

$wallet->deposit(100);
$wallet->balance; // 100
$wallet->balanceFloatNum; // 1.00
```

## How to get the right wallet?

```php
$myWallet = $user->getWallet('my-wallet');
$myWallet->balance; // 100
$myWallet->balanceFloatNum; // 1.00
```

## Default Wallet + MultiWallet

Is it possible to use the default wallet and multi-wallets at the same time? Yes.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet, HasWallets;
}
```

How to get the default wallet?

```php
$wallet = $user->wallet;
$wallet->balance; // 10
$wallet->balanceFloatNum; // 0.10
```

It worked! 
