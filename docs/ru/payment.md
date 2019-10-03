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
         * Если покупку можно совершить всего 1 раз, то
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

## Процесс оплаты

Найдем пользователя и проверим его баланс.

```php
$user = User::first();
$user->balance; // int(100)
```

Найдет товар и проверим стоимость.

```php
$item = Item::first();
$item->getAmountProduct($user); // int(100)
```

Процесс оплаты.

```php
$user->pay($item);
$user->balance; // int(0)
```

Что будет, если у пользователя нет средств?
Тоже что и при [выводе](withdraw#failed).

```php
$user->balance; // int(0)
$user->pay($item);
// throw an exception
```

Как проверить, что пользователь купил товар?

```php
(bool)$user->paid($item); // bool(true)
```

## Безопасная оплата

Чтобы не писать `try` и `catch` используй `safePay` метод.

```php
if ($user->safePay($item)) {
  // Данный метод не броит exception
}
```

Просто работает!
