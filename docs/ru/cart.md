## Пользователь

Добавим `CanPay` trait и `Customer` interface в модель User.

> Трейт `CanPay` уже наследует `HasWallet`, повторное использование вызовет ошибку.

```php
use Bavix\Wallet\Traits\CanPay;
use Bavix\Wallet\Interfaces\Customer;

class User extends Model implements Customer
{
    use CanPay;
}
```

## Товар

Добавим `HasWallet` trait и `Product` interface в модель Item.

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
        return round($this->price * 100);
    }

    public function getMetaProduct(): ?array
    {
        return [
            'title' => $this->title, 
            'description' => 'Purchase of Product #' . $this->getUniqueId(),
        ];
    }
    
    public function getUniqueId(): string
    {
        return (string)$this->getKey();
    }
}
```

## Как заполнить корзину

Найдем пользователя и проверим его баланс.

```php
$user = User::first();
$user->balance; // int(0)
```

Приступим к покупкам.

```php
use Bavix\Wallet\Objects\Cart;

$list = [
    'potato' => 3,
    'carrot' => 10,
];

$products = Item::query()
    ->whereIn('slug', ['potato', 'carrot'])
    ->get();

$cart = Cart::make();
foreach ($products as $product) {
    // add product's
    $cart->addItem($product, $list[$product->slug]);
}

$user->deposit($cart->getTotal());
$user->balanceFloat; // float(151.27)

(bool)$user->payCart($cart); // true
$user->balanceFloat; // float(0)
```

Это работает!
