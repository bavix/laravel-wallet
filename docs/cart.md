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

Starting from version 9.x there are two product interfaces:
- For an unlimited number of products (`ProductInterface`);
- For a limited number of products (`ProductLimitedInterface`);

An example with an unlimited number of products:
```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductInterface;

class Item extends Model implements ProductInterface
{
    use HasWallet;

    public function getAmountProduct(Customer $customer): int|string
    {
        return round($this->price * 100);
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
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductLimitedInterface;

class Item extends Model implements ProductLimitedInterface
{
    use HasWallet;

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        /**
         * This is where you implement the constraint logic. 
         * 
         * If the service can be purchased once, then
         *  return !$customer->paid($this);
         */
        return true; 
    }
    
    public function getAmountProduct(Customer $customer): int|string
    {
        return round($this->price * 100);
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

## Fill the cart

Find the user and check the balance.

```php
$user = User::first();
$user->balance; // 0
```

Let's start shopping.

```php
use Bavix\Wallet\Objects\Cart;

$list = [
    'potato' => 3,
    'carrot' => 10,
];

$products = Item::query()
    ->whereIn('slug', ['potato', 'carrot'])
    ->get();

$cart = app(Cart::class);
foreach ($products as $product) {
    $cart = $cart->withItem($product, quantity: $list[$product->slug]);
}

$cartTotal = $cart->getTotal($user); // 15127
$user->deposit($cartTotal); 
$user->balanceInt; // 15127
$user->balanceFloat; // 151.27

$cart = $cart->withItem(current($products), pricePerItem: 500); // 15127+500
$user->deposit(500);
$user->balanceInt; // 15627
$user->balanceFloat; // 156.27

(bool)$user->payCart($cart); // true
$user->balanceFloat; // 0
```

It worked! 
