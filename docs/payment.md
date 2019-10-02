## User Model

Add the `CanPay` trait and `Customer` interface to your User model.

> The trait `CanPay` already inherits `HasWallet`, reuse will cause an error.

```php
use Bavix\Wallet\Traits\CanPay;
use Bavix\Wallet\Interfaces\Customer;

class User extends Model implements Customer
{
    use CanPay;
}
```

## Item Model

Add the `HasWallet` trait and `Product` interface to Item model.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Interfaces\Customer;

class Item extends Model implements Product
{
    use HasWallet;

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = null): bool
    {
        /**
         * If the service can be purchased once, then
         *  return !$customer->paid($this);
         */
        return true; 
    }

    public function getAmountProduct(Customer $customer): int
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

## Proceed to purchase

Find the user and check the balance.

```php
$user = User::first();
$user->balance; // int(100)
```

Find the goods and check the cost.

```php
$item = Item::first();
$item->getAmountProduct($user); // int(100)
```

The user can buy a product, buy...

```php
$user->pay($item);
$user->balance; // int(0)
```

What happens if the user does not have the funds?
The same as with the [withdrawal](withdraw#failed).

```php
$user->balance; // int(0)
$user->pay($item);
// throw an exception
```

The question arises, how do you know that the product is purchased?

```php
(bool)$user->paid($item); // bool(true)
```

## Safe Pay

To not write `try` and `catch` use `safePay` method.

```php
if ($user->safePay($item)) {
  // try to buy again )
}
```

It worked! 
