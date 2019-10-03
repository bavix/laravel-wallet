# Купить бесплатно

Иногда возникают ситуации, когда необходимо подарить товар.
Для этих случаев существует этот метод.

---

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

## Покупка

Найдем пользователя и проверим его баланс.

```php
$user = User::first();
$user->balance; // int(100)
```

Найдем товар, проверим стоимость и баланс.

```php
$item = Item::first();
$item->getAmountProduct($user); // int(100)
$item->balance; // int(0)
```

Переходим к покупке.

```php
$user->payFree($item);
(bool)$user->paid($item); // bool(true)
$user->balance; // int(100)
$item->balance; // int(0)
```

Баланс пользователя и товара остался прежним.

Просто работает!
