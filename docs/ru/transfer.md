# Переводы

Перевод осуществляется с помощью операций 
[Deposit](deposit) и 
[Withdraw](withdraw).

Обычно операции делают вывод из "неоткуда" в "некуда",
но в данном случае операцию подписывает таблица `transfers`.

---

## Пользователь

Подготовим модель, добавив `HasWallet` trait и `Wallet` interface.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet;
}
```

## Перевод

Найдем пользователей:

```php
$first = User::first(); 
$last = User::orderBy('id', 'desc')->first(); // last user
$first->getKey() !== $last->getKey(); // true
```

Проверим их баланс.

```php
$first->balance; // int(100)
$last->balance; // int(0)
```

Сделаем перевод от первого второму.

```php
$first->transfer($last, 5); 
$first->balance; // int(95)
$last->balance; // int(5)
```

## Заставить перевести.

Операция необходима, если в вашей 
системе разрешено уходить в минус.

```php
$first->balance; // int(100)
$last->balance; // int(0)
```

Сделаем перевод от первого второму.

```php
$first->forceTransfer($last, 500); 
$first->balance; // int(-400)
$last->balance; // int(500)
```

Просто работает.
