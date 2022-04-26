# Transfer between wallets

Transfer in our system are two well-known [Deposit](deposit) and [Withdraw](withdraw) 
operations that are performed in one transaction.

The transfer takes place between wallets.

---

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

## Make a Transfer

Find user:

```php
$first = User::first(); 
$last = User::orderBy('id', 'desc')->first(); // last user
$first->getKey() !== $last->getKey(); // true
```

Create new wallets for users.
```php
$firstWallet = $first->createWallet(['name' => 'First User Wallet']);
$lastWallet = $last->createWallet(['name' => 'Second User Wallet']);

$firstWallet->deposit(100);
$firstWallet->balance; // 100
$lastWallet->balance; // 0
```

The transfer will be from the first user to the last.

```php
$firstWallet->transfer($lastWallet, 5); 
$firstWallet->balance; // 95
$lastWallet->balance; // 5
```

It worked! 

## Force Transfer

Check the user's balance.

```php
$firstWallet->balance; // 100
$lastWallet->balance; // 0
```

The transfer will be from the first user to the second.

```php
$firstWallet->forceTransfer($lastWallet, 500); 
$firstWallet->balance; // -400
$lastWallet->balance; // 500
```

It worked!

## Change the meta and confirmation.

Check the user's balance.

```php
$firstWallet->balanceInt; // 1_000
$secondWallet->balanceInt; // 0
```

We will execute the transfer, but without confirmation of the withdrawal of funds.

```php
use Bavix\Wallet\External\Dto\Extra;
use Bavix\Wallet\External\Dto\Option;

/** @var $firstWallet \Bavix\Wallet\Interfaces\Wallet */
$transfer = $firstWallet->transfer($secondWallet, 500, new Extra(
    deposit: ['message' => 'Hello, secondWallet!'],
    withdraw: new Option(meta: ['something' => 'anything'], confirmed: false)
));

$transfer->withdraw->meta; // ['something' => 'anything']
$transfer->withdraw->confirmed; // false

$transfer->deposit->meta; // ['message' => 'Hello, secondWallet!']
$transfer->deposit->confirmed; // true

$firstWallet->balanceInt; // 1_000
$secondWallet->balanceInt; // 500
```

It worked! 
