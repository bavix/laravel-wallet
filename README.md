![Laravel Wallet](https://user-images.githubusercontent.com/5111255/48687709-a7c2fa00-ebd3-11e8-8714-c4f3efe93f02.png)

[![Maintainability](https://api.codeclimate.com/v1/badges/588400f5f40cbbf3a8ab/maintainability)](https://codeclimate.com/github/bavix/laravel-wallet/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/588400f5f40cbbf3a8ab/test_coverage)](https://codeclimate.com/github/bavix/laravel-wallet/test_coverage) [![Financial Contributors on Open Collective](https://opencollective.com/laravel-wallet/all/badge.svg?label=financial+contributors)](https://opencollective.com/laravel-wallet) [![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fbavix%2Flaravel-wallet%2Fmaster)](https://packagist.org/packages/bavix/laravel-wallet)

[![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v)](https://packagist.org/packages/bavix/laravel-wallet) [![Latest Unstable Version](https://poser.pugx.org/bavix/laravel-wallet/v/unstable)](https://packagist.org/packages/bavix/laravel-wallet) [![License](https://poser.pugx.org/bavix/laravel-wallet/license)](https://packagist.org/packages/bavix/laravel-wallet) [![composer.lock](https://poser.pugx.org/bavix/laravel-wallet/composerlock)](https://packagist.org/packages/bavix/laravel-wallet)

[![Sparkline](https://stars.medv.io/bavix/laravel-wallet.svg)](https://stars.medv.io/bavix/laravel-wallet)

laravel-wallet - Easy work with virtual wallet.

[[Benchmark](https://github.com/bavix/laravel-wallet-benchmark/)] 
[[Documentation](https://bavix.github.io/laravel-wallet/)] 
[[Get Started](https://bavix.github.io/laravel-wallet/#/basic-usage)] 

* **Vendor**: bavix
* **Package**: laravel-wallet
* **Version**: [![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v)](https://packagist.org/packages/bavix/laravel-wallet)
* **PHP Version**: 8.0+
* **Laravel Version**: `9.x`
* **[Composer](https://getcomposer.org/):** `composer require bavix/laravel-wallet`

### Support Policy

| Version    | Laravel        | PHP             | Release date | End of improvements | End of support |
|------------|----------------|-----------------|--------------|---------------------|----------------|
| 7.x        | ^6.0,^7.0,^8.0 | 7.4,8.0,8.1     | Nov 25, 2021 | Mar 1, 2022         | Sep 6, 2022    |
| 8.x        | ^9.0           | 8.0,8.1         | Feb 8, 2022  | May 1, 2022         | Jun 1, 2022    |
| 9.x [LTS]  | ^9.0,^10.0     | 8.0,8.1,8.2,8.3 | May 2, 2022  | Feb 1, 2023         | Feb 6, 2024    |
| 10.x [LTS] | ^10.0,^11.0    | 8.1,8.2,8.3     | Jul 8, 2023  | May 1, 2024         | Feb 4, 2025    |

### Upgrade Guide

To perform the migration, you will be [helped by the instruction](https://bavix.github.io/laravel-wallet/#/upgrade-guide).

### Community

I want to create a cozy place for developers using the wallet package. This will help you find bugs faster, get feedback and discuss ideas.

![telegram](https://user-images.githubusercontent.com/5111255/188698261-1306c729-de56-4cff-8190-fb5fbcb1b266.jpg)

Telegram: [@laravel_wallet](https://t.me/laravel_wallet)

### Extensions

| Extension                                                 | Description                                                                |
|-----------------------------------------------------------|----------------------------------------------------------------------------|
| [Swap](https://github.com/bavix/laravel-wallet-swap)      | Addition to the laravel-wallet library for quick setting of exchange rates |
| [uuid](https://github.com/bavix/laravel-wallet-uuid)      | Addition to laravel-wallet to support model uuid keys                      | 
| [Warm Up](https://github.com/bavix/laravel-wallet-warmup) | Addition to the laravel-wallet library for refresh balance wallets         | 

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
$user->balanceInt; // 0

$user->deposit(10);
$user->balance; // 10
$user->balanceInt; // int(10)

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

Add the `HasWallet` trait and interface to `Item` model.

Starting from version 9.x there are two product interfaces:
- For an unlimited number of products (`ProductInterface`);
- For a limited number of products (`ProductLimitedInterface`);

An example with an unlimited number of products:
```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductInterface;

class Item extends Model implements ProductInterface
{
    use HasWallet;

    public function getAmountProduct(Customer $customer): int|string
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
}
```

Example with a limited number of products:
```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\ProductLimitedInterface;

class Item extends Model implements ProductLimitedInterface
{
    use HasWallet;

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        /**
         * This is where you implement the constraint logic. 
         * 
         * If the service can be purchased once, then
         *  return !$customer->paid($this);
         */
        return true; 
    }
    
    public function getAmountProduct(Customer $customer): int|string
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
}
```

I do not recommend using the limited interface when working with a shopping cart. 
If you are working with a shopping cart, then you should override the `PurchaseServiceInterface` interface. 
With it, you can check the availability of all products with one request, there will be no N-queries in the database.

Proceed to purchase.

```php
$user = User::first();
$user->balance; // 100

$item = Item::first();
$user->pay($item); // If you do not have enough money, throw an exception
var_dump($user->balance); // 0

if ($user->safePay($item)) {
  // try to buy again
}

var_dump((bool)$user->paid($item)); // bool(true)

var_dump($user->refund($item)); // bool(true)
var_dump((bool)$user->paid($item)); // bool(false)
```

### Eager Loading

```php
// When working with one wallet
User::with('wallet');

// When using the multi-wallet functionality
User::with('wallets');
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

### Performance Comparison

All versions:
|            Name            |  7.3   |  8.4   |  9.2   |  9.3   |  9.4   |  9.5   |  9.6   |  10.0  |
|----------------------------|--------|--------|--------|--------|--------|--------|--------|--------|
| Atomic:Blocks              | -      | -      | 860ms  | 561ms  | 697ms  | 722ms  | 775ms  | 586ms  |
| Cart:EagerLoaderPay        | 26.9s  | 881ms  | 818ms  | 674ms  | 829ms  | 800ms  | 768ms  | 627ms  |
| Cart:Pay                   | 1.67s  | 651ms  | 534ms  | 426ms  | 529ms  | 492ms  | 543ms  | 355ms  |
| Cart:PayFree               | 1.6s   | 540ms  | 448ms  | 346ms  | 435ms  | 455ms  | 487ms  | 385ms  |
| Cart:PayOneItemXPieces     | 712ms  | 156ms  | 89ms   | 79.9ms | 80.9ms | 99.4ms | 87.5ms | 60.6ms |
| Gift:Gift                  | 61ms   | 71.5ms | 85.7ms | 70.5ms | 75.1ms | 82.8ms | 84.4ms | 66.2ms |
| Gift:Refund                | 126ms  | 150ms  | 166ms  | 134ms  | 153ms  | 161ms  | 160ms  | 127ms  |
| Solo:Deposit               | 37.1ms | 38.9ms | 47.5ms | 39ms   | 42.2ms | 46.8ms | 41.1ms | 20.3ms |
| Solo:EagerLoading          | 1.11s  | 1.45s  | 1.43s  | 1.12s  | 1.42s  | 1.47s  | 1.51s  | 1.19s  |
| Solo:ForceWithdraw         | 36.4ms | 39.1ms | 45.9ms | 39.2ms | 41.6ms | 46ms   | 42.1ms | 20.5ms |
| Solo:GetBalance            | 27.7ms | 30.6ms | 38.1ms | 32.4ms | 32ms   | 37.8ms | 32ms   | 8.44ms |
| Solo:Transfer              | 55.8ms | 58.4ms | 67ms   | 53.1ms | 57.3ms | 63.8ms | 60.2ms | 35.5ms |
| Solo:Withdraw              | 41.5ms | 44.5ms | 53.1ms | 44.1ms | 45.9ms | 51.7ms | 47.7ms | 25.9ms |
| State:InTransaction        | 670ms  | 721ms  | 869ms  | 622ms  | 829ms  | 854ms  | 598ms  | 504ms  |
| State:RefreshInTransaction | 51.9ms | 55.8ms | 63ms   | 53.8ms | 63.5ms | 63.6ms | 60.2ms | 41.3ms |
| State:TransactionRollback  | 40.2ms | 41.8ms | 48.6ms | 41.3ms | 43.4ms | 48.7ms | 45.1ms | 24.1ms |

Major compare:
|            Name            |  7.x   |  8.x   |  9.x   |  10.x  |
|----------------------------|--------|--------|--------|--------|
| Atomic:Blocks              | -      | -      | 704ms  | 586ms  |
| Cart:EagerLoaderPay        | 26.9s  | 881ms  | 780ms  | 627ms  |
| Cart:Pay                   | 1.67s  | 651ms  | 492ms  | 355ms  |
| Cart:PayFree               | 1.6s   | 540ms  | 425ms  | 385ms  |
| Cart:PayOneItemXPieces     | 712ms  | 156ms  | 81.9ms | 60.6ms |
| Gift:Gift                  | 61ms   | 71.5ms | 74.6ms | 66.2ms |
| Gift:Refund                | 126ms  | 150ms  | 152ms  | 127ms  |
| Solo:Deposit               | 37.1ms | 38.9ms | 41ms   | 20.3ms |
| Solo:EagerLoading          | 1.11s  | 1.45s  | 1.38s  | 1.19s  |
| Solo:ForceWithdraw         | 36.4ms | 39.1ms | 41.4ms | 20.5ms |
| Solo:GetBalance            | 27.7ms | 30.6ms | 32.1ms | 8.44ms |
| Solo:Transfer              | 55.8ms | 58.4ms | 57.1ms | 35.5ms |
| Solo:Withdraw              | 41.5ms | 44.5ms | 46.1ms | 25.9ms |
| State:InTransaction        | 670ms  | 721ms  | 754ms  | 504ms  |
| State:RefreshInTransaction | 51.9ms | 55.8ms | 60.9ms | 41.3ms |
| State:TransactionRollback  | 40.2ms | 41.8ms | 44.1ms | 24.1ms |

Table generated using [benchmark](https://github.com/bavix/laravel-wallet-benchmark/). [Pull Request](https://github.com/bavix/laravel-wallet-benchmark/pull/40).

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
