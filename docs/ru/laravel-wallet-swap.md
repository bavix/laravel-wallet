## Laravel Wallet Swap

## Composer

Рекомендуем установку используя [Composer](https://getcomposer.org/).

В корне вашего проекта запустите:

```bash
composer req bavix/laravel-wallet-swap
```

### Пользователь
Для работы библиотеки нужны мульти-кошельки, 
поскольку транзакции будут между кошельками одного пользователя.

```php
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Traits\HasWallet;

class User extends Model implements Wallet
{
    use HasWallet, HasWallets;
}
```

### Простой пример
Находим кошельки пользователя и переводим с одного на другой.

```php
$usd = $user->getWallet('usd');
$rub = $user->getWallet('rub');

$usd->balance; // int(200)
$rub->balance; // int(0)

$usd->exchange($rub, 10);
$usd->balance; // int(190)
$rub->balance; // int(622)
```

Это работает.
