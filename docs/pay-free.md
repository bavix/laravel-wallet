## User Model

Add the `CanPay` trait and `Customer` interface to your User model.

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

    public function canBuy(Customer $customer, bool $force = false): bool
    {
        /**
         * If the service can be purchased once, then
         *  return !$customer->paid($this);
         */
        return true; 
    }

    public function getAmountProduct(): int
    {
        return 100;
    }

    public function getMetaProduct(): ?array
    {
        return [
            'title' => $this->title, 
            'description' => 'Purchase of Product #' . $this->id, 
            'price' => $this->getAmountProduct(),
        ];
    }
}
```

## Pay Free

Find the user and check the balance.

```php
$user = User::first();
$user->balance; // int(100)
```

Find the goods and check the cost.

```php
$item = Item::first();
$item->getAmountProduct(); // int(100)
$item->balance; // int(0)
```

Purchase!

```php
$user->payFree($item);
(bool)$user->paid($item); // bool(true)
$user->balance; // int(100)
$item->balance; // int(0)
```

It worked! 
