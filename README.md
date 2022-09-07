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
| 9.x [LTS]  | ^9.0           | 8.0,8.1     | 1 Feb 2023          | 6 Nov 2023     |

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
|            Name            |  6.0   |  6.1   |   6.2   |  7.0   |  7.1   |  7.2   |  7.3   |  8.0   |  8.1   |  8.2   |  8.3   |  8.4   |  9.0   |  9.1   |  9.2   |  9.3   |
|----------------------------|--------|--------|---------|--------|--------|--------|--------|--------|--------|--------|--------|--------|--------|--------|--------|--------|
| Atomic:Blocks              | -      | -      | -       | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      | -      | 743ms  | 629ms  |
| Cart:EagerLoaderPay        | 6.68s  | 5.06s  | 1m21.9s | 35.1s  | 38.6s  | 42.1s  | 34.7s  | 30.6s  | 29.3s  | 30.9s  | 1.18s  | 885ms  | 730ms  | 753ms  | 666ms  | 729ms  |
| Cart:Pay                   | 1.9s   | 1.48s  | 4.87s   | 2.07s  | 2.18s  | 2.42s  | 2.22s  | 1.97s  | 1.85s  | 1.98s  | 573ms  | 585ms  | 427ms  | 446ms  | 457ms  | 494ms  |
| Cart:PayFree               | 2.04s  | 1.57s  | 5.07s   | 2.02s  | 2.06s  | 2.27s  | 2.07s  | 1.87s  | 1.74s  | 1.86s  | 542ms  | 564ms  | 367ms  | 413ms  | 363ms  | 408ms  |
| Cart:PayOneItemXPieces     | 904ms  | 696ms  | 2.74s   | 928ms  | 1.04s  | 1.12s  | 915ms  | 816ms  | 773ms  | 823ms  | 166ms  | 170ms  | 69.2ms | 75.2ms | 69.8ms | 74.8ms |
| Gift:Gift                  | 96.7ms | 74ms   | 96.1ms  | 61.1ms | 63.5ms | 71.4ms | 71.6ms | 67.5ms | 64.8ms | 65.7ms | 61.3ms | 66.3ms | 65.3ms | 66.9ms | 60ms   | 69.8ms |
| Gift:Refund                | 259ms  | 198ms  | 250ms   | 138ms  | 149ms  | 190ms  | 164ms  | 175ms  | 162ms  | 172ms  | 146ms  | 140ms  | 139ms  | 141ms  | 127ms  | 143ms  |
| Solo:Deposit               | 41.7ms | 31.8ms | 40.6ms  | 32ms   | 30.7ms | 35.2ms | 37.1ms | 38.8ms | 32.1ms | 33.8ms | 28.7ms | 30.1ms | 29.7ms | 32.2ms | 29ms   | 30.2ms |
| Solo:EagerLoading          | 1.99s  | 1.49s  | 2.01s   | 1.28s  | 1.26s  | 3.49s  | 1.51s  | 9.14s  | 8.92s  | 9.3s   | 9.19s  | 1.35s  | 1.31s  | 1.25s  | 1.17s  | 1.18s  |
| Solo:ForceWithdraw         | 42.1ms | 31.3ms | 40.4ms  | 31.7ms | 31.5ms | 35.2ms | 37.2ms | 37.1ms | 32.4ms | 33.2ms | 28.3ms | 30.6ms | 29.6ms | 31.7ms | 28.7ms | 30.6ms |
| Solo:GetBalance            | 25.7ms | 19.5ms | 25.9ms  | 21.9ms | 20.3ms | 23.5ms | 26.1ms | 24.3ms | 21.3ms | 22.4ms | 19.4ms | 21.2ms | 20.7ms | 22.2ms | 19.6ms | 23.2ms |
| Solo:Transfer              | 86.3ms | 63.6ms | 80.9ms  | 52.8ms | 55ms   | 61.2ms | 62.7ms | 60.8ms | 53.9ms | 57ms   | 47.6ms | 51.9ms | 46.4ms | 48.5ms | 45.2ms | 48.1ms |
| State:InTransaction        | 2.52s  | 1.87s  | 2.21s   | 1.05s  | 868ms  | 907ms  | 924ms  | 836ms  | 760ms  | 811ms  | 666ms  | 729ms  | 733ms  | 782ms  | 719ms  | 706ms  |
| State:RefreshInTransaction | -      | -      | -       | -      | 47.2ms | 52.8ms | 52.8ms | 49.9ms | 47.3ms | 47.7ms | 39.8ms | 44ms   | 43.3ms | 45.7ms | 42.3ms | 47.6ms |
| State:TransactionRollback  | -      | -      | -       | -      | 38.1ms | 42.8ms | 41.2ms | 39.9ms | 36.6ms | 37.2ms | 31.5ms | 35ms   | 33.8ms | 34.9ms | 31ms   | 36.4ms |

Major compare:
|            Name            |  6.x   |  7.x   |  8.x   |  9.x   |
|----------------------------|--------|--------|--------|--------|
| Cart:EagerLoaderPay        | 31.2s  | 37.6s  | 18.6s  | 719ms  |
| Cart:Pay                   | 2.75s  | 2.22s  | 1.39s  | 456ms  |
| Cart:PayFree               | 2.9s   | 2.1s   | 1.31s  | 388ms  |
| Cart:PayOneItemXPieces     | 1.45s  | 1s     | 550ms  | 72.2ms |
| Gift:Gift                  | 88.9ms | 66.9ms | 65.1ms | 65.5ms |
| Gift:Refund                | 236ms  | 160ms  | 159ms  | 138ms  |
| Solo:Deposit               | 38ms   | 33.8ms | 32.7ms | 30.3ms |
| Solo:EagerLoading          | 1.83s  | 1.89s  | 7.58s  | 1.23s  |
| Solo:ForceWithdraw         | 37.9ms | 33.9ms | 32.3ms | 30.2ms |
| Solo:GetBalance            | 23.7ms | 22.9ms | 21.7ms | 21.4ms |
| Solo:Transfer              | 76.9ms | 57.9ms | 54.3ms | 47.1ms |
| State:InTransaction        | 2.2s   | 937ms  | 760ms  | 735ms  |
| State:RefreshInTransaction | -      | -      | 45.8ms | 44.7ms |
| State:TransactionRollback  | -      | -      | 36ms   | 34ms   |

Table generated using [benchmark](https://github.com/bavix/laravel-wallet-benchmark/). [Pull Request](https://github.com/bavix/laravel-wallet-benchmark/pull/24).

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
