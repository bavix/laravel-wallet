![Laravel Wallet](https://user-images.githubusercontent.com/5111255/48687709-a7c2fa00-ebd3-11e8-8714-c4f3efe93f02.png)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bavix/laravel-wallet/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bavix/laravel-wallet/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/build.png?b=master)](https://scrutinizer-ci.com/g/bavix/laravel-wallet/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![Mutation testing badge](https://badge.stryker-mutator.io/github.com/bavix/laravel-wallet/master)](https://packagist.org/packages/bavix/laravel-wallet)

[![Package Rank](https://phppackages.org/p/bavix/laravel-wallet/badge/rank.svg)](https://packagist.org/packages/bavix/laravel-wallet)
[![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v/stable)](https://packagist.org/packages/bavix/laravel-wallet)
[![Latest Unstable Version](https://poser.pugx.org/bavix/laravel-wallet/v/unstable)](https://packagist.org/packages/bavix/laravel-wallet)
[![License](https://poser.pugx.org/bavix/laravel-wallet/license)](https://packagist.org/packages/bavix/laravel-wallet)
[![composer.lock](https://poser.pugx.org/bavix/laravel-wallet/composerlock)](https://packagist.org/packages/bavix/laravel-wallet)

laravel-wallet - Easy work with virtual wallet.

[[Documentation](https://bavix.github.io/laravel-wallet/)] 
[[Get Started](https://bavix.github.io/laravel-wallet/#/basic-usage)] 

[[Документация](https://bavix.github.io/laravel-wallet/#/ru/)] 
[[Как начать](https://bavix.github.io/laravel-wallet/#/ru/basic-usage)] 

* **Vendor**: bavix
* **Package**: laravel-wallet
* **Version**: [![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v/stable)](https://packagist.org/packages/bavix/laravel-wallet)
* **PHP Version**: 7.2+ 
* **Laravel Version**: `5.5`, `5.6`, `5.7`, `5.8`, `6.0`
* **[Composer](https://getcomposer.org/):** `composer require bavix/laravel-wallet`

### Upgrade Guide

To perform the migration, you will be [helped by the instruction](https://bavix.github.io/laravel-wallet/#/upgrade-guide).

### Extensions

| Extension | Description | 
| ----- | ----- | 
| [Swap](https://github.com/bavix/laravel-wallet-swap) | Addition to the laravel-wallet library for quick setting of exchange rates | 
| [Vacuum](https://github.com/bavix/laravel-wallet-vacuum) | Addition to the laravel-wallet library for quick fix race condition | 

### Usage
Add the `HasWallet` trait and `Wallet` interface to model.
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

$user->forceWithdraw(200, ['description' => 'payment of taxes']);
$user->balance; // int(-191)
```

### Purchases

Add the `CanPay` trait and `Customer` interface to your `User` model.
```php
use Bavix\Wallet\Traits\CanPay;
use Bavix\Wallet\Interfaces\Customer;

class User extends Model implements Customer
{
    use CanPay;
}
```

Add the `HasWallet` trait and `Product` interface to `Item` model.
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
User::with('wallet');
```

### How to work with fractional numbers?
Add the `HasWalletFloat` trait and `WalletFloat` interface to model.
```php
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet, WalletFloat
{
    use HasWalletFloat;
}
```

Now we make transactions.

```php
$user = User::first();
$user->balance; // int(100)
$user->balanceFloat; // float(1.00)

$user->depositFloat(1.37);
$user->balance; // int(237)
$user->balanceFloat; // float(2.37)
```

---
Supported by

[![Supported by JetBrains](https://cdn.rawgit.com/bavix/development-through/46475b4b/jetbrains.svg)](https://www.jetbrains.com/)
