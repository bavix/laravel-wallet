# Переводы между кошельками

Перевод осуществляется с помощью операций 
[Deposit](deposit) и 
[Withdraw](withdraw).

Обычно операции делают вывод из "неоткуда" в "некуда",
но в данном случае операцию подписывает таблица `transfers`.

---

## Пользователь

Проготовим модель, добавив `HasWallet`, `HasWallets` trait's и `Wallet` interface.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet, HasWallets;
}
```

## Сделаем перевод

Найдем пользователей.

```php
$first = User::first(); 
$last = User::orderBy('id', 'desc')->first(); // last user
$first->getKey() !== $last->getKey(); // true
```

Создадим пользователям кошельки.
```php
$name = 'New Wallet';
$firstWallet = $first->createWallet(compact('name'));
$lastWallet = $last->createWallet(compact('name'));

$firstWallet->deposit(100);
$firstWallet->balance; // int(100)
$lastWallet->balance; // int(0)
```

Выполним перевод от первого второму.

```php
$firstWallet->transfer($lastWallet, 5); 
$firstWallet->balance; // int(95)
$lastWallet->balance; // int(5)
```

## Принудительный перевод

Проверим баланс.

```php
$firstWallet->balance; // int(100)
$lastWallet->balance; // int(0)
```

Выполним перевод от первого второму.

```php
$firstWallet->forceTransfer($lastWallet, 500); 
$firstWallet->balance; // int(-400)
$lastWallet->balance; // int(500)
```

Просто работает!
