## User Model

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

## Item Model

Добавим `HasWallet` trait и `Product` interface в модель Item.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Taxable;

class Item extends Model implements Product, Taxable
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
    
    public function getFeePercent() : float
    {
        return 0.03; // 3%    
    }
}
```

## Оплата с налогом

Находим пользователя и проверяем его баланс.

```php
$user = User::first();
$user->balance; // int(103)
```

Деньги есть, это хорошо. Проверим стоимость товара.

```php
$item = Item::first();
$item->getAmountProduct($user); // int(100)
```

Товар стоит 100 бубликов. Значит налог составит 3 бублика.
Пользователю хватает средств для покупки товара. 

```php
$user->pay($item); // success, 100 (product) + 3 (fee) = 103
$user->balance; // int(0)
```

## Минимальный налог

Добавим trait `MinimalTaxable` в модель `Item`.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Product;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\MinimalTaxable;

class Item extends Model implements Product, MinimalTaxable
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
    
    public function getFeePercent() : float
    {
        return 0.03; // 3%    
    }
    
    public function getMinimalFee() : int
    {
        return 5; // 3%, minimum int(5)    
    }
}
```

#### Успешная операция

Находим пользователя и проверяем баланс.

```php
$user = User::first();
$user->balance; // int(105)
```

Денег хватает, проверяем стоимость товара.

```php
$item = Item::first();
$item->getAmountProduct($user); // int(100)
```

Налог 3%, а минимальный налог составляет 5 бубликов. 
Нашему пользователю хватает средств для покупки товара.

```php
$user->pay($item); // успешно, 100 (product) + 5 (minimal fee) = 105
$user->balance; // int(0)
```

#### Ошибочная операция

Находим пользователя и проверяем баланс.

```php
$user = User::first();
$user->balance; // int(103)
```

Находим товар.

```php
$item = Item::first();
$item->getAmountProduct($user); // int(100)
```

Налог составит 5 бубликов, а у пользователя только 103 бублика.
Возникнет ошибка при попытке оплаты, воспользуемся `safePay` чтобы это проверить.

```php
$user->safePay($item); // ошибка, 100 (product) + 5 (minimal fee) = 105
$user->balance; // int(103)
```

Это работает!
