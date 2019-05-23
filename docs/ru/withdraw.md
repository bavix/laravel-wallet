# Вывод средств

Вывод средств одна из операций i/o.
Если депозит начисляет средства из неоткуда, 
то вывод средств туда же.

Но запись о вводе/выводе средств остается в системе.

---

- [Пользователь](#user-model)
- [Вывод](#make-a-withdraw)
- [Заставить вывести](#force-withdraw)
- [Ошибки](#failed)

## Пользователь

Подготовим модель добавив `HasWallet` trait и `Wallet` interface.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet;
}
```

## Вывод

Найдем пользователя:

```php
$user = User::first(); 
```

Проверим баланс.

```php
$user->balance; // int(100)
```

Баланс не пустой, значит можем вывести.

```php
$user->withdraw(10); 
$user->balance; // int(90)
```

Просто работает!

## Заставить вывести.

Иногда требуется заставить вывести средства.
К примеру, баланс пользователя не позволяет 
вывести 101 монету, а нужно. 
К примеру, штраф за нарушение правил сайта.

```php
$user->balance; // int(100)
$user->forceWithdraw(101);
$user->balance; // int(-1)
```

## Что будет, если средств не хватает?

Этот пункт не касается `forceWithdraw`.

Может быть 2 ситуации:

- Баланс пользователя =0, тогда
`Bavix\Wallet\Exceptions\BalanceIsEmpty`
- Баланс пользователя >0 и монет не хватает, то
`Bavix\Wallet\Exceptions\InsufficientFunds`
