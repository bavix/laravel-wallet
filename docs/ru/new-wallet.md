# Как добавить кошелек

У пользователя виртуальных кошельков может быть сколько угодно.
Единственное ограничение это уникальный `slug` для них.

---

## Пользователь

Добавим `HasWallet`, `HasWallets` trait's и `Wallet` interface в модель.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet, HasWallets;
}
```

## Создадим кошелек

Найдем пользователя.

```php
$user = User::first(); 
```

Проверим баланс.

```php
$user->balance; // 0
```

`$user->balance` - это алиас вызова `$user->wallet->balance`,
кошелек по умолчанию.

Создадим новый кошелек.

```php
$wallet = $user->createWallet([
    'name' => 'New Wallet',
    'slug' => 'my-wallet',
]);

$wallet->deposit(100);
$wallet->balance; // 100

$user->deposit(10); 
$user->balance; // 10
```

## Как обратиться к новому кошельку?

```php
$myWallet = $user->getWallet('my-wallet');
$myWallet->balance; // 100
```

## Как обратиться к кошельку по умолчанию?

```php
$wallet = $user->wallet;
$wallet->balance; // 10
```

Просто работает!
