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
| 9.x [LTS]  | ^9.0           | 8.0,8.1,8.2 | 1 Feb 2023          | 6 Nov 2023     |

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
|            Name            |  6.1   |   6.2   |  7.2   |  7.3   |  8.3   |  8.4   |  9.0   |  9.1   |  9.2   |  9.3   |  9.4   |  9.5   |
|----------------------------|--------|---------|--------|--------|--------|--------|--------|--------|--------|--------|--------|--------|
| Atomic:Blocks              | -      | -       | -      | -      | -      | -      | -      | -      | 723ms  | 657ms  | 682ms  | 636ms  |
| Cart:EagerLoaderPay        | 5.23s  | 1m30.2s | 39.3s  | 29.2s  | 1.35s  | 762ms  | 789ms  | 580ms  | 666ms  | 793ms  | 714ms  | 643ms  |
| Cart:Pay                   | 1.7s   | 5.18s   | 2.21s  | 1.75s  | 711ms  | 520ms  | 516ms  | 359ms  | 410ms  | 537ms  | 460ms  | 368ms  |
| Cart:PayFree               | 1.78s  | 5.41s   | 2.08s  | 1.71s  | 607ms  | 473ms  | 434ms  | 309ms  | 341ms  | 430ms  | 385ms  | 346ms  |
| Cart:PayOneItemXPieces     | 695ms  | 3.02s   | 1.04s  | 783ms  | 170ms  | 147ms  | 84.2ms | 70.5ms | 72.9ms | 76.1ms | 69.6ms | 69ms   |
| Gift:Gift                  | 77.8ms | 97ms    | 62.5ms | 55.8ms | 71.5ms | 60.7ms | 79.4ms | 61ms   | 63ms   | 70.9ms | 64.8ms | 62.6ms |
| Gift:Refund                | 228ms  | 254ms   | 159ms  | 129ms  | 176ms  | 125ms  | 165ms  | 116ms  | 127ms  | 157ms  | 137ms  | 122ms  |
| Solo:Deposit               | 33.1ms | 40.8ms  | 29.7ms | 28.2ms | 34.2ms | 31.8ms | 35ms   | 31.3ms | 30.3ms | 29.3ms | 29.4ms | 28.9ms |
| Solo:EagerLoading          | 1.72s  | 1.98s   | 2.69s  | 1.1s   | 10.3s  | 1.16s  | 1.52s  | 973ms  | 1.09s  | 1.29s  | 1.09s  | 1.13s  |
| Solo:ForceWithdraw         | 33ms   | 39.1ms  | 29.6ms | 28.3ms | 33.7ms | 32.4ms | 36.7ms | 31.1ms | 30.2ms | 30.6ms | 29.7ms | 28.1ms |
| Solo:GetBalance            | 21.9ms | 23.8ms  | 16.9ms | 18.2ms | 21.9ms | 21.2ms | 24.2ms | 22.7ms | 20.8ms | 20.4ms | 21.5ms | 19.3ms |
| Solo:Transfer              | 66.5ms | 84.8ms  | 54.9ms | 49.5ms | 57.8ms | 51.5ms | 56.1ms | 46.1ms | 46.5ms | 48.4ms | 44.2ms | 45.1ms |
| Solo:Withdraw              | 43.3ms | 54.6ms  | 38.5ms | 34.5ms | 40.1ms | 37.8ms | 43.1ms | 36.4ms | 36.3ms | 36.5ms | 34.7ms | 34.6ms |
| State:InTransaction        | 1.91s  | 2.41s   | 786ms  | 721ms  | 806ms  | 659ms  | 924ms  | 643ms  | 722ms  | 786ms  | 750ms  | 690ms  |
| State:RefreshInTransaction | -      | -       | 49.5ms | 37.1ms | 45.2ms | 42.6ms | 52ms   | 46.6ms | 39.7ms | 40.1ms | 41.1ms | 39.2ms |
| State:TransactionRollback  | -      | -       | 35.8ms | 31.7ms | 37.4ms | 35.4ms | 41.2ms | 33.9ms | 33.3ms | 35.9ms | 32.8ms | 32.5ms |

Major compare:
|            Name            |  6.x   |  7.x   |  8.x   |  9.x   |
|----------------------------|--------|--------|--------|--------|
| Cart:EagerLoaderPay        | 42.7s  | 33.5s  | 1.06s  | 701ms  |
| Cart:Pay                   | 3.89s  | 2.07s  | 627ms  | 430ms  |
| Cart:PayFree               | 3.43s  | 1.98s  | 548ms  | 360ms  |
| Cart:PayOneItemXPieces     | 1.74s  | 882ms  | 160ms  | 72.1ms |
| Gift:Gift                  | 86.7ms | 57.4ms | 66.7ms | 64.9ms |
| Gift:Refund                | 246ms  | 143ms  | 154ms  | 133ms  |
| Solo:Deposit               | 35.2ms | 28.6ms | 32.8ms | 30.7ms |
| Solo:EagerLoading          | 1.92s  | 2s     | 4.92s  | 1.13s  |
| Solo:ForceWithdraw         | 34.3ms | 28.7ms | 32.9ms | 30.5ms |
| Solo:GetBalance            | 22.4ms | 17.9ms | 21.6ms | 21.6ms |
| Solo:Transfer              | 77.5ms | 50.2ms | 53.6ms | 46.8ms |
| Solo:Withdraw              | 48ms   | 35.3ms | 38.7ms | 36.3ms |
| State:InTransaction        | 2.32s  | 759ms  | 715ms  | 739ms  |
| State:RefreshInTransaction | -      | 41.7ms | 44.2ms | 43.1ms |
| State:TransactionRollback  | -      | 32.8ms | 36.6ms | 34.1ms |

Table generated using [benchmark](https://github.com/bavix/laravel-wallet-benchmark/). [Pull Request](https://github.com/bavix/laravel-wallet-benchmark/pull/30).

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
