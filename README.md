![Laravel Wallet](https://user-images.githubusercontent.com/5111255/48687709-a7c2fa00-ebd3-11e8-8714-c4f3efe93f02.png)

[![Maintainability](https://api.codeclimate.com/v1/badges/588400f5f40cbbf3a8ab/maintainability)](https://codeclimate.com/github/bavix/laravel-wallet/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/588400f5f40cbbf3a8ab/test_coverage)](https://codeclimate.com/github/bavix/laravel-wallet/test_coverage)
[![Financial Contributors on Open Collective](https://opencollective.com/laravel-wallet/all/badge.svg?label=financial+contributors)](https://opencollective.com/laravel-wallet) [![Mutation testing badge](https://badge.stryker-mutator.io/github.com/bavix/laravel-wallet/master)](https://packagist.org/packages/bavix/laravel-wallet)

[![Package Rank](https://phppackages.org/p/bavix/laravel-wallet/badge/rank.svg)](https://packagist.org/packages/bavix/laravel-wallet)
[![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v)](https://packagist.org/packages/bavix/laravel-wallet)
[![Latest Unstable Version](https://poser.pugx.org/bavix/laravel-wallet/v/unstable)](https://packagist.org/packages/bavix/laravel-wallet)
[![License](https://poser.pugx.org/bavix/laravel-wallet/license)](https://packagist.org/packages/bavix/laravel-wallet)
[![composer.lock](https://poser.pugx.org/bavix/laravel-wallet/composerlock)](https://packagist.org/packages/bavix/laravel-wallet)

[![Sparkline](https://stars.medv.io/bavix/laravel-wallet.svg)](https://stars.medv.io/bavix/laravel-wallet)

laravel-wallet - Easy work with virtual wallet.

[[Documentation](https://bavix.github.io/laravel-wallet/)] 
[[Get Started](https://bavix.github.io/laravel-wallet/#/basic-usage)] 

[[Документация](https://bavix.github.io/laravel-wallet/#/ru/)] 
[[Как начать](https://bavix.github.io/laravel-wallet/#/ru/basic-usage)] 

* **Vendor**: bavix
* **Package**: laravel-wallet
* **Version**: [![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v)](https://packagist.org/packages/bavix/laravel-wallet)
* **PHP Version**: 7.3+ (if you are using version 5.x then 7.2+)
* **Laravel Version**: `5.5`, `5.6`, `5.7`, `5.8`, `6.x`, `7.x`, `8.x`
* **[Composer](https://getcomposer.org/):** `composer require bavix/laravel-wallet`

### Upgrade Guide

> Starting with version 5.x, support for Laravel 5 has been discontinued.
> Update laravel or use version 4.x.

To perform the migration, you will be [helped by the instruction](https://bavix.github.io/laravel-wallet/#/upgrade-guide).

### Extensions

| Extension | Description | 
| ----- | ----- | 
| [Swap](https://github.com/bavix/laravel-wallet-swap) | Addition to the laravel-wallet library for quick setting of exchange rates | 
| [Vacuum](https://github.com/bavix/laravel-wallet-vacuum) | Addition to the laravel-wallet library for quick fix race condition | 

> Since version 6.2 the Vacuum package is built in and no longer requires additional steps.

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
$user->balance; // 0

$user->deposit(10);
$user->balance; // 10

$user->withdraw(1);
$user->balance; // 9

$user->forceWithdraw(200, ['description' => 'payment of taxes']);
$user->balance; // -191
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

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        /**
         * If the service can be purchased once, then
         *  return !$customer->paid($this);
         */
        return true; 
    }
    
    public function getAmountProduct(Customer $customer)
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
$user->balance; // 100

$item = Item::first();
$user->pay($item); // If you do not have enough money, throw an exception
var_dump($user->balance); // 0

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
$user->balance; // 100
$user->balanceFloat; // 1.00

$user->depositFloat(1.37);
$user->balance; // 237
$user->balanceFloat; // 2.37
```

---
Supported by

[![Supported by JetBrains](https://cdn.rawgit.com/bavix/development-through/46475b4b/jetbrains.svg)](https://www.jetbrains.com/)

## Contributors

### Code Contributors

This project exists thanks to all the people who contribute. [[Contribute](CONTRIBUTING.md)].
<a href="https://github.com/bavix/laravel-wallet/graphs/contributors"><img src="https://opencollective.com/laravel-wallet/contributors.svg?width=890&button=false" /></a>

### Financial Contributors

Become a financial contributor and help us sustain our community. [[Contribute](https://opencollective.com/laravel-wallet/contribute)]

#### Individuals

<a href="https://opencollective.com/laravel-wallet"><img src="https://opencollective.com/laravel-wallet/individuals.svg?width=890"></a>

#### Organizations

Support this project with your organization. Your logo will show up here with a link to your website. [[Contribute](https://opencollective.com/laravel-wallet/contribute)]

<a href="https://opencollective.com/laravel-wallet/organization/0/website"><img src="https://opencollective.com/laravel-wallet/organization/0/avatar.svg"></a>
<a href="https://opencollective.com/laravel-wallet/organization/1/website"><img src="https://opencollective.com/laravel-wallet/organization/1/avatar.svg"></a>
<a href="https://opencollective.com/laravel-wallet/organization/2/website"><img src="https://opencollective.com/laravel-wallet/organization/2/avatar.svg"></a>
<a href="https://opencollective.com/laravel-wallet/organization/3/website"><img src="https://opencollective.com/laravel-wallet/organization/3/avatar.svg"></a>
<a href="https://opencollective.com/laravel-wallet/organization/4/website"><img src="https://opencollective.com/laravel-wallet/organization/4/avatar.svg"></a>
<a href="https://opencollective.com/laravel-wallet/organization/5/website"><img src="https://opencollective.com/laravel-wallet/organization/5/avatar.svg"></a>
<a href="https://opencollective.com/laravel-wallet/organization/6/website"><img src="https://opencollective.com/laravel-wallet/organization/6/avatar.svg"></a>
<a href="https://opencollective.com/laravel-wallet/organization/7/website"><img src="https://opencollective.com/laravel-wallet/organization/7/avatar.svg"></a>
<a href="https://opencollective.com/laravel-wallet/organization/8/website"><img src="https://opencollective.com/laravel-wallet/organization/8/avatar.svg"></a>
<a href="https://opencollective.com/laravel-wallet/organization/9/website"><img src="https://opencollective.com/laravel-wallet/organization/9/avatar.svg"></a>
