# laravel-wallet

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bavix/laravel-wallet/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bavix/laravel-wallet/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/build.png?b=master)](https://scrutinizer-ci.com/g/bavix/laravel-wallet/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

[![Package Rank](https://phppackages.org/p/bavix/laravel-wallet/badge/rank.svg)](https://packagist.org/packages/bavix/laravel-wallet)
[![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v/stable)](https://packagist.org/packages/bavix/laravel-wallet)
[![Latest Unstable Version](https://poser.pugx.org/bavix/laravel-wallet/v/unstable)](https://packagist.org/packages/bavix/laravel-wallet)
[![License](https://poser.pugx.org/bavix/laravel-wallet/license)](https://packagist.org/packages/bavix/laravel-wallet)
[![composer.lock](https://poser.pugx.org/bavix/laravel-wallet/composerlock)](https://packagist.org/packages/bavix/laravel-wallet)

laravel-wallet - Easy work with virtual wallet.

* **Vendor**: bavix
* **Package**: laravel-wallet
* **Version**: [![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v/stable)](https://packagist.org/packages/bavix/laravel-wallet)
* **PHP Version**: 7.1+ 
* **[Composer](https://getcomposer.org/):** `composer require bavix/laravel-wallet`

### Run Migrations
Publish the migrations with this artisan command:
```bash
php artisan vendor:publish --tag=laravel-wallet-migrations
```

### Configuration
You can publish the config file with this artisan command:
```bash
php artisan vendor:publish --tag=laravel-wallet-config
```

### Usage
Add the `HasWallet` trait to model.
```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet;
}
```

Now we make transactions.

```php
$user = User::first();
$user->balance; // int(0)

$user->deposit(10);
$user->balance; // int(10)

$user->withdraw(1);
$user->balance; // int(9)

$user->forceWithdraw(200);
$user->balance; // int(-191)
```

### Purchases

Add the `CanBePaid` trait to your `User` model.
```php
use Bavix\Wallet\Traits\CanBePaid;
use Bavix\Wallet\Interfaces\Customer;

class User extends Model implements Customer
{
    use CanBePaid;
}
```

Add the `HasWallet` trait to `Item` model.
```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Product;

class Item extends Model implements Product
{
    use HasWallet;

    public function canBuy(Customer $customer): bool
    {
        /**
         * If the service can be purchased once, then
         *  return !$customer->paid($this);
         */
        return true; 
    }
    
    public function getAmountProduct(): int
    {
        return 100;
    }

    public function getMetaProduct(): ?array
    {
        return [
            'title' => $this->title, 
            'description' => $this->description, 
            'price' => $this->getAmountProduct(),
        ];
    }
}
```

Proceed to purchase.

```php
$user = User::first();
$user->balance; // int(100)

$item = Item::first();
$user->pay($item); // If you do not have enough money, throw an exception
var_dump($user->balance); // int(0)

if ($user->safePay($item)) {
  // try to buy again )
}

var_dump((bool)$user->paid($item)); // bool(true)

var_dump($user->refund($item)); // bool(true)
var_dump((bool)$user->paid($item)); // bool(false)
```

### Eager Loading

```php
User::with('balance');
```

---
Supported by

[![Supported by JetBrains](https://cdn.rawgit.com/bavix/development-through/46475b4b/jetbrains.svg)](https://www.jetbrains.com/)
