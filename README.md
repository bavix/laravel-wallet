![Laravel Wallet](https://user-images.githubusercontent.com/5111255/48687709-a7c2fa00-ebd3-11e8-8714-c4f3efe93f02.png)

[![Maintainability](https://api.codeclimate.com/v1/badges/588400f5f40cbbf3a8ab/maintainability)](https://codeclimate.com/github/bavix/laravel-wallet/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/588400f5f40cbbf3a8ab/test_coverage)](https://codeclimate.com/github/bavix/laravel-wallet/test_coverage) [![Financial Contributors on Open Collective](https://opencollective.com/laravel-wallet/all/badge.svg?label=financial+contributors)](https://opencollective.com/laravel-wallet) [![Mutation testing badge](https://badge.stryker-mutator.io/github.com/bavix/laravel-wallet/master)](https://packagist.org/packages/bavix/laravel-wallet)

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

| Version    | Laravel        | PHP         | Release date | End of improvements | End of support |
|------------|----------------|-------------|--------------|---------------------|----------------|
| 7.x        | ^6.0,^7.0,^8.0 | 7.4,8.0,8.1 | Nov 25, 2021 | Mar 1, 2022         | Sep 6, 2022    |
| 8.x        | ^9.0           | 8.0,8.1     | Feb 8, 2022  | May 1, 2022         | Jun 1, 2022    |
| 9.x [LTS]  | ^9.0,^10.0     | 8.0,8.1,8.2 | May 2, 2022  | Feb 1, 2023         | Feb 6, 2024    |
| 10.x [LTS] | ^10.0          | 8.1,8.2     | Jul 8, 2023  | May 1, 2024         | Feb 3, 2025    |

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
|            Name            |  7.3   |  8.4   |  9.0   |  9.1   |  9.2   |  9.3   |  9.4   |  9.5   |  9.6   |  10.0  |
|----------------------------|--------|--------|--------|--------|--------|--------|--------|--------|--------|--------|
| Atomic:Blocks              | -      | -      | -      | -      | 903ms  | 703ms  | 680ms  | 649ms  | 668ms  | 668ms  |
| Cart:EagerLoaderPay        | 27.2s  | 872ms  | 634ms  | 672ms  | 626ms  | 652ms  | 568ms  | 583ms  | 647ms  | 625ms  |
| Cart:Pay                   | 1.69s  | 601ms  | 410ms  | 429ms  | 405ms  | 410ms  | 381ms  | 365ms  | 381ms  | 355ms  |
| Cart:PayFree               | 1.62s  | 507ms  | 340ms  | 351ms  | 340ms  | 338ms  | 318ms  | 328ms  | 348ms  | 370ms  |
| Cart:PayOneItemXPieces     | 724ms  | 160ms  | 73.6ms | 66.8ms | 67.3ms | 76.3ms | 76.3ms | 67.3ms | 76ms   | 59.4ms |
| Gift:Gift                  | 60.7ms | 76.2ms | 67.8ms | 61.2ms | 62.4ms | 67.8ms | 67.9ms | 62ms   | 68.5ms | 62.7ms |
| Gift:Refund                | 134ms  | 152ms  | 129ms  | 135ms  | 131ms  | 131ms  | 124ms  | 126ms  | 134ms  | 122ms  |
| Solo:Deposit               | 27ms   | 41.4ms | 29.6ms | 28ms   | 27.9ms | 32.6ms | 37ms   | 28.5ms | 34.6ms | 17.8ms |
| Solo:EagerLoading          | 1.16s  | 1.43s  | 1.22s  | 1.16s  | 1.09s  | 1.05s  | 877ms  | 1.03s  | 1.21s  | 1.14s  |
| Solo:ForceWithdraw         | 27.3ms | 41.4ms | 30.3ms | 28.2ms | 28ms   | 31.9ms | 36.3ms | 29.2ms | 33.5ms | 18.2ms |
| Solo:GetBalance            | 16.8ms | 29.6ms | 21.2ms | 19.6ms | 20.1ms | 23.3ms | 28.9ms | 20.5ms | 24.1ms | 7.16ms |
| Solo:Transfer              | 48.2ms | 61.1ms | 46.2ms | 43.1ms | 43.5ms | 48.8ms | 49.9ms | 43.2ms | 50.6ms | 32.7ms |
| Solo:Withdraw              | 33ms   | 47.4ms | 35.6ms | 33.6ms | 34.2ms | 36.1ms | 40.9ms | 34.2ms | 41.4ms | 23.3ms |
| State:InTransaction        | 795ms  | 842ms  | 725ms  | 809ms  | 759ms  | 710ms  | 710ms  | 709ms  | 568ms  | 570ms  |
| State:RefreshInTransaction | 36.4ms | 54.6ms | 40ms   | 36.7ms | 37.3ms | 44.4ms | 52.4ms | 38.3ms | 47.2ms | 33.8ms |
| State:TransactionRollback  | 30.5ms | 44.5ms | 33.4ms | 31.5ms | 31.1ms | 34.7ms | 38.8ms | 32.3ms | 38.8ms | 21.7ms |

Major compare:
|            Name            |  7.x   |  8.x   |  9.x   |  10.x  |
|----------------------------|--------|--------|--------|--------|
| Atomic:Blocks              | -      | -      | -      | 668ms  |
| Cart:EagerLoaderPay        | 27.2s  | 872ms  | 632ms  | 625ms  |
| Cart:Pay                   | 1.69s  | 601ms  | 401ms  | 355ms  |
| Cart:PayFree               | 1.62s  | 507ms  | 338ms  | 370ms  |
| Cart:PayOneItemXPieces     | 724ms  | 160ms  | 71.5ms | 59.4ms |
| Gift:Gift                  | 60.7ms | 76.2ms | 66.2ms | 62.7ms |
| Gift:Refund                | 134ms  | 152ms  | 131ms  | 122ms  |
| Solo:Deposit               | 27ms   | 41.4ms | 30.4ms | 17.8ms |
| Solo:EagerLoading          | 1.16s  | 1.43s  | 1.13s  | 1.14s  |
| Solo:ForceWithdraw         | 27.3ms | 41.4ms | 30.5ms | 18.2ms |
| Solo:GetBalance            | 16.8ms | 29.6ms | 21.9ms | 7.16ms |
| Solo:Transfer              | 48.2ms | 61.1ms | 46.2ms | 32.7ms |
| Solo:Withdraw              | 33ms   | 47.4ms | 36ms   | 23.3ms |
| State:InTransaction        | 795ms  | 842ms  | 716ms  | 570ms  |
| State:RefreshInTransaction | 36.4ms | 54.6ms | 40.8ms | 33.8ms |
| State:TransactionRollback  | 30.5ms | 44.5ms | 33.9ms | 21.7ms |

Table generated using [benchmark](https://github.com/bavix/laravel-wallet-benchmark/). [Pull Request](https://github.com/bavix/laravel-wallet-benchmark/pull/36).

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
