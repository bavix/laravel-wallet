## Laravel Wallet Swap

## Composer

The recommended installation method is using [Composer](https://getcomposer.org/).

In your project root just run:

```bash
composer req bavix/laravel-wallet-swap
```

### User model
We need a simple model with the ability to work multi-wallets.

```php
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\HasWallets;
use Bavix\Wallet\Traits\HasWallet;

class User extends Model implements Wallet
{
    use HasWallet, HasWallets;
}
```

### Simple example
Let's create wallets with currency:
```php
$usd = $user->createWallet([
    'name' => 'My Dollars',
    'slug' => 'usd',
    'meta' => ['currency' => 'USD'],
]);

$rub = $user->createWallet([
    'name' => 'My Ruble',
    'slug' => 'rub',
    'meta' => ['currency' => 'RUB'],
]);
```

Find wallets and exchange from one to another.

```php
$rub = $user->getWallet('rub');
$usd = $user->getWallet('usd');

$usd->balance; // 200
$rub->balance; // 0

$usd->exchange($rub, 10);
$usd->balance; // 190
$rub->balance; // 622
```

It worked! 
