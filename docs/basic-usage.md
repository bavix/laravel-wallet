## Simple Wallet

[User Simple](_include/models/user_simple.md ':include')

## Simple wallet transactions

The package is built on simple transactions:
  - deposit - replenishment of the wallet;
  - withdraw - withdrawal from the wallet;
  
Consider an example:

```php
$user = User::first();
$user->balance; // 0

$user->deposit(10);
$user->balance; // 10

$user->withdraw(1);
$user->balance; // 9

$user->forceWithdraw(200, ['description' => 'payment of taxes']);
$user->balance; // -191
```

## Purchases

Add the `CanPay` trait and `Customer` interface to your `User` model.

> The trait `CanPay` already inherits `HasWallet`, reuse will cause an error.

```php
use Bavix\Wallet\Traits\CanPay;
use Bavix\Wallet\Interfaces\Customer;

class User extends Model implements Customer
{
    use CanPay;
}
```

Add the `HasWallet` trait and `Product` interface to `Item` model.
```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Interfaces\Customer;

class Item extends Model implements Product
{
    use HasWallet;

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        /**
         * If the service can be purchased once, then
         *  return !$customer->paid($this);
         */
        return true; 
    }
    
    public function getAmountProduct(Customer $customer)
    {
        return 100;
    }

    public function getMetaProduct(): ?array
    {
        return [
            'title' => $this->title, 
            'description' => 'Purchase of Product #' . $this->id,
        ];
    }
    
    public function getUniqueId(): string
    {
        return (string)$this->getKey();
    }
}
```

Proceed to purchase.

```php
$user = User::first();
$user->balance; // 100

$item = Item::first();
$user->pay($item); // If you do not have enough money, throw an exception
var_dump($user->balance); // 0

if ($user->safePay($item)) {
  // try to buy again )
}

var_dump((bool)$user->paid($item)); // bool(true)

var_dump($user->refund($item)); // bool(true)
var_dump((bool)$user->paid($item)); // bool(false)
```

[Eager Loading](_include/eager_loading.md ':include')

## How to work with fractional numbers?
Add the `HasWalletFloat` trait and `WalletFloat` interface to model.
```php
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet, WalletFloat
{
    use HasWalletFloat;
}
```

Now we make transactions.

```php
$user = User::first();
$user->balance; // 100
$user->balanceFloat; // 1.00

$user->depositFloat(1.37);
$user->balance; // 237
$user->balanceFloat; // 2.37
```
