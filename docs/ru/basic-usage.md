## Простые операции

Добавим `HasWallet` trait и `Wallet` interface в модель.
```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet;
}
```

Сейчас произведем операции по кошельку.

```php
$user = User::first();
$user->balance; // int(0)

$user->deposit(10);
$user->balance; // int(10)

$user->withdraw(1);
$user->balance; // int(9)

$user->forceWithdraw(200, ['description' => 'payment of taxes']);
$user->balance; // int(-191)
```

## Покупки

Добавим `CanPay` trait и `Customer` interface в модель `User`.
Это наш покупатель.

> Трейт `CanPay` уже наследует `HasWallet`, повторное использование вызовет ошибку.

```php
use Bavix\Wallet\Traits\CanPay;
use Bavix\Wallet\Interfaces\Customer;

class User extends Model implements Customer
{
    use CanPay;
}
```

Добавим `HasWallet` trait и `Product` interface в модель `Item`.
Это товар.

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
}
```

Перейдем к процессу покупки.

```php
$user = User::first();
$user->balance; // int(100)

$item = Item::first();
$user->pay($item); // Если у пользователя не достаточно средств будет exception
var_dump($user->balance); // int(0)

if ($user->safePay($item)) {
  // Так делается безопасная покупка!
  // В этом случае, при не достатке средсв, 
  //      метод вернет `false`
}

var_dump((bool)$user->paid($item)); // bool(true)

var_dump($user->refund($item)); // bool(true)
var_dump((bool)$user->paid($item)); // bool(false)
```

## Нетерпеливая Загрузка

Иногда нужна нетерпеливая (жадная) загрузка.
К примеру, нам нужно вывести список пользователей 
с их балансом. Чтобы не делать n+1 запрос, просто добавьте `with`.

```php
User::with('wallet');
```

## А как работать с дробными числами?
Добавьте `HasWalletFloat` trait и `WalletFloat` interface в вашу модель.
```php
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet, WalletFloat
{
    use HasWalletFloat;
}
```

Используем.

```php
$user = User::first();
$user->balance; // int(100)
$user->balanceFloat; // float(1.00)

$user->depositFloat(1.37);
$user->balance; // int(237)
$user->balanceFloat; // float(2.37)
```

Просто работает.
