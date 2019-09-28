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
Find wallets and exchange from one to another.

```php
$usd = $user->getWallet('usd');
$rub = $user->getWallet('rub');

$usd->balance; // int(200)
$rub->balance; // int(0)

$usd->exchange($rub, 10);
$usd->balance; // int(190)
$rub->balance; // int(622)
```

It worked! 
