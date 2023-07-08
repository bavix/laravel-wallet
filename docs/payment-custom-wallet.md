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

Add the `HasWallet` trait and interface to `Item` model.
If we want to achieve multi wallets for a product, then we need to add `HasWallets`.

Starting from version 9.x there are two product interfaces:
- For an unlimited number of products (`ProductInterface`);
- For a limited number of products (`ProductLimitedInterface`);

An example with an unlimited number of products:
```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductInterface;

class Item extends Model implements ProductInterface
{
    use HasWallet, HasWallets;

    public function getAmountProduct(Customer $customer): int|string
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
}
```

Example with a limited number of products:
```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductLimitedInterface;

class Item extends Model implements ProductLimitedInterface
{
    use HasWallet, HasWallets;

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        return true; 
    }
    
    public function getAmountProduct(Customer $customer): int|string
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
}
```

I do not recommend using the limited interface when working with a shopping cart.
If you are working with a shopping cart, then you should override the `PurchaseServiceInterface` interface.
With it, you can check the availability of all products with one request, there will be no N-queries in the database.

## Proceed to purchase

Find the user and check the balance.

```php
$user = User::first();
$user->balance; // 100
```

Find the goods and check the cost.

```php
$item = Item::first();
$item->getAmountProduct($user); // 100

$receiving = $item->createWallet([
    'name' => 'Dollar',
    'meta' => [
        'currency' => 'USD',
    ],
]);
```

The user can buy a product, buy...

```php
$cart = app(Cart::class)
    ->withItem($item, receiving: $receiving)
;

$user->payCart($cart);
$user->balance; // 0

$receiving->balanceInt; // $100
```

It worked! 
