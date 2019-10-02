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

## Santa Claus, give gifts

Find the user's and check the balance.

```php
$first = User::first(); 
$last = User::orderBy('id', 'desc')->first(); // last user
$first->getKey() !== $last->getKey(); // true

$first->balance; // int(115)
$last->balance; // int(0)
```

One user wants to give a gift to another.
Find the product.

```php
$item = Item::first();
$item->getAmountProduct($first); // int(100)
$item->balance; // int(0)
```

The first user buys the product and gives it.

> If the product uses the `Taxable` interface, then Santa will pay tax

```php
$first->gift($last, $item);
(bool)$last->paid($item, true); // bool(true)
$first->balance; // int(15)
$last->balance; // int(0)
$item->balance; // int(100)
```

It worked! 
