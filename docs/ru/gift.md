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

## Дед мороз дарит подарки

Находим дедушку мороза и счастливчика.

```php
$first = User::first(); 
$last = User::orderBy('id', 'desc')->first(); // last user
$first->getKey() !== $last->getKey(); // true

$first->balance; // int(115)
$last->balance; // int(0)
```

У дедушки есть деньги на подарок.
Находим сам подарок (товар).

```php
$item = Item::first();
$item->getAmountProduct($first); // int(100)
$item->balance; // int(0)
```

Дедушка мороз дарит подарок "ребёнку".

> Если товар использует `Taxable` интерфейс, то дедушка заплатит налог

```php
$first->gift($last, $item);
(bool)$last->paid($item, true); // bool(true)
$first->balance; // int(15)
$last->balance; // int(0)
$item->balance; // int(100)
```

Это работает!
