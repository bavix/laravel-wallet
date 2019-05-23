# Make a refund

---

- [User Model](#user-model)
- [Item Model](#item-model)
- [Make a refund](#refund)

<a name="user-model"></a>
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

<a name="item-model"></a>
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

<a name="refund"></a>
## Make a refund

Find the user and check the balance.

```php
$user = User::first();
$user->balance; // int(0)
```

Find the goods and check the balance.

```php
$item = Item::first();
$item->balance; // int(100)
```

Return of funds!

```php
(bool)$user->paid($item); // bool(true)
(bool)$user->refund($item); // bool(true)
$item->balance; // int(0)
$user->balance; // int(100)
```

It worked! 
