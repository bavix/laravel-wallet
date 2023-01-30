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

| Version    | Laravel        | PHP         | End of improvements | End of support |
|------------|----------------|-------------|---------------------|----------------|
| 7.x        | ^6.0,^7.0,^8.0 | 7.4,8.0,8.1 | 1 Mar 2022          | 6 Sep 2022     |
| 8.x        | ^9.0           | 8.0,8.1     | 1 May 2022          | 1 Jun 2022     |
| 9.x [LTS]  | ^9.0,^10.0     | 8.0,8.1,8.2 | 1 Feb 2023          | 6 Nov 2023     |

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
|            Name            |  7.3   |  8.4   |  9.0   |  9.1   |  9.2   |  9.3   |  9.4   |  9.5   |  9.6   |
|----------------------------|--------|--------|--------|--------|--------|--------|--------|--------|--------|
| Atomic:Blocks              | -      | -      | -      | -      | 789ms  | 548ms  | 586ms  | 664ms  | 593ms  |
| Cart:EagerLoaderPay        | 31.2s  | 781ms  | 782ms  | 694ms  | 668ms  | 651ms  | 607ms  | 647ms  | 580ms  |
| Cart:Pay                   | 1.99s  | 527ms  | 492ms  | 432ms  | 478ms  | 409ms  | 377ms  | 428ms  | 331ms  |
| Cart:PayFree               | 1.87s  | 459ms  | 404ms  | 367ms  | 394ms  | 342ms  | 329ms  | 380ms  | 338ms  |
| Cart:PayOneItemXPieces     | 846ms  | 140ms  | 74ms   | 68.3ms | 72.1ms | 71.3ms | 62.5ms | 79.1ms | 66.1ms |
| Gift:Gift                  | 65.4ms | 61.4ms | 70.4ms | 61ms   | 64.1ms | 60.8ms | 55.6ms | 69.5ms | 59.8ms |
| Gift:Refund                | 149ms  | 125ms  | 155ms  | 136ms  | 142ms  | 124ms  | 118ms  | 143ms  | 127ms  |
| Solo:Deposit               | 30.4ms | 32.9ms | 32.3ms | 26.3ms | 31.7ms | 31.8ms | 24.4ms | 33.4ms | 25.5ms |
| Solo:EagerLoading          | 1.29s  | 1.32s  | 1.47s  | 1.2s   | 1.18s  | 1.06s  | 975ms  | 1.15s  | 1.04s  |
| Solo:ForceWithdraw         | 30.6ms | 32.1ms | 32.3ms | 26.1ms | 31.7ms | 31.6ms | 24.7ms | 33.3ms | 25.8ms |
| Solo:GetBalance            | 19.4ms | 22.7ms | 22.4ms | 17.5ms | 23.4ms | 24.7ms | 17.2ms | 23.7ms | 17.4ms |
| Solo:Transfer              | 55.9ms | 48.5ms | 51ms   | 42.4ms | 46.8ms | 45ms   | 38.7ms | 49.6ms | 39.9ms |
| Solo:Withdraw              | 38.3ms | 35.8ms | 39ms   | 31.9ms | 37ms   | 36ms   | 29.7ms | 39.4ms | 31.7ms |
| State:InTransaction        | 771ms  | 619ms  | 772ms  | 746ms  | 749ms  | 624ms  | 647ms  | 787ms  | 520ms  |
| State:RefreshInTransaction | 42.3ms | 44.1ms | 43.8ms | 35.4ms | 46.4ms | 44.5ms | 33.7ms | 45ms   | 36.3ms |
| State:TransactionRollback  | 36.6ms | 34.1ms | 36.5ms | 28.9ms | 35.3ms | 35.5ms | 27.9ms | 37.3ms | 29.9ms |

Major compare:
|            Name            |  7.x   |  8.x   |  9.x   |
|----------------------------|--------|--------|--------|
| Cart:EagerLoaderPay        | 31.2s  | 781ms  | 646ms  |
| Cart:Pay                   | 1.99s  | 527ms  | 398ms  |
| Cart:PayFree               | 1.87s  | 459ms  | 349ms  |
| Cart:PayOneItemXPieces     | 846ms  | 140ms  | 72ms   |
| Gift:Gift                  | 65.4ms | 61.4ms | 62.3ms |
| Gift:Refund                | 149ms  | 125ms  | 127ms  |
| Solo:Deposit               | 30.4ms | 32.9ms | 30.3ms |
| Solo:EagerLoading          | 1.29s  | 1.32s  | 1.11s  |
| Solo:ForceWithdraw         | 30.6ms | 32.1ms | 30.2ms |
| Solo:GetBalance            | 19.4ms | 22.7ms | 20.7ms |
| Solo:Transfer              | 55.9ms | 48.5ms | 46.2ms |
| Solo:Withdraw              | 38.3ms | 35.8ms | 36.5ms |
| State:InTransaction        | 771ms  | 619ms  | 669ms  |
| State:RefreshInTransaction | 42.3ms | 44.1ms | 40.4ms |
| State:TransactionRollback  | 36.6ms | 34.1ms | 34.1ms |

Table generated using [benchmark](https://github.com/bavix/laravel-wallet-benchmark/). [Pull Request](https://github.com/bavix/laravel-wallet-benchmark/pull/32).

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
