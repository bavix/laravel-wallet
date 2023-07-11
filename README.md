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
|            Name            |  7.3   |  8.4   |  9.2   |  9.3   |  9.4   |  9.5   |  9.6   |  10.0  |
|----------------------------|--------|--------|--------|--------|--------|--------|--------|--------|
| Atomic:Blocks              | -      | -      | 913ms  | 734ms  | 723ms  | 853ms  | 677ms  | 669ms  |
| Cart:EagerLoaderPay        | 25.6s  | 800ms  | 650ms  | 650ms  | 641ms  | 837ms  | 666ms  | 655ms  |
| Cart:Pay                   | 1.64s  | 580ms  | 421ms  | 424ms  | 421ms  | 524ms  | 390ms  | 382ms  |
| Cart:PayFree               | 1.55s  | 491ms  | 344ms  | 333ms  | 337ms  | 465ms  | 361ms  | 377ms  |
| Cart:PayOneItemXPieces     | 697ms  | 149ms  | 74.5ms | 77.1ms | 78.9ms | 94.3ms | 77.6ms | 60.4ms |
| Gift:Gift                  | 61ms   | 69.3ms | 67.1ms | 69ms   | 70.3ms | 91.4ms | 70.2ms | 65.8ms |
| Gift:Refund                | 130ms  | 144ms  | 133ms  | 138ms  | 139ms  | 182ms  | 140ms  | 131ms  |
| Solo:Deposit               | 35.9ms | 36.5ms | 37.7ms | 37.9ms | 38.6ms | 43.8ms | 39.8ms | 20.5ms |
| Solo:EagerLoading          | 1.1s   | 1.33s  | 1.12s  | 1.1s   | 1.06s  | 1.49s  | 1.28s  | 1.17s  |
| Solo:ForceWithdraw         | 36.2ms | 37.2ms | 37.6ms | 38ms   | 38.3ms | 44.8ms | 40ms   | 20.2ms |
| Solo:GetBalance            | 26.6ms | 27.7ms | 29.9ms | 29.2ms | 31.8ms | 32.8ms | 32ms   | 8.39ms |
| Solo:Transfer              | 54.9ms | 56.4ms | 51.1ms | 51.8ms | 53.1ms | 65ms   | 53.2ms | 35ms   |
| Solo:Withdraw              | 41.3ms | 42ms   | 42ms   | 41.5ms | 43ms   | 50.8ms | 44.9ms | 25.9ms |
| State:InTransaction        | 758ms  | 778ms  | 773ms  | 742ms  | 779ms  | 1.03s  | 566ms  | 571ms  |
| State:RefreshInTransaction | 51.6ms | 52.4ms | 52.2ms | 52.3ms | 58.8ms | 61.6ms | 53.5ms | 43ms   |
| State:TransactionRollback  | 38.8ms | 39.7ms | 39.8ms | 41.3ms | 42ms   | 51.3ms | 43ms   | 23.9ms |

Major compare:
|            Name            |  7.x   |  8.x   |  9.x   |  10.x  |
|----------------------------|--------|--------|--------|--------|
| Atomic:Blocks              | -      | -      | 753ms  | 669ms  |
| Cart:EagerLoaderPay        | 25.6s  | 800ms  | 661ms  | 655ms  |
| Cart:Pay                   | 1.64s  | 580ms  | 424ms  | 382ms  |
| Cart:PayFree               | 1.55s  | 491ms  | 347ms  | 377ms  |
| Cart:PayOneItemXPieces     | 697ms  | 149ms  | 78.5ms | 60.4ms |
| Gift:Gift                  | 61ms   | 69.3ms | 70.6ms | 65.8ms |
| Gift:Refund                | 130ms  | 144ms  | 139ms  | 131ms  |
| Solo:Deposit               | 35.9ms | 36.5ms | 39.3ms | 20.5ms |
| Solo:EagerLoading          | 1.1s   | 1.33s  | 1.16s  | 1.17s  |
| Solo:ForceWithdraw         | 36.2ms | 37.2ms | 38.8ms | 20.2ms |
| Solo:GetBalance            | 26.6ms | 27.7ms | 31.1ms | 8.39ms |
| Solo:Transfer              | 54.9ms | 56.4ms | 53.5ms | 35ms   |
| Solo:Withdraw              | 41.3ms | 42ms   | 43.6ms | 25.9ms |
| State:InTransaction        | 758ms  | 778ms  | 754ms  | 571ms  |
| State:RefreshInTransaction | 51.6ms | 52.4ms | 59.2ms | 43ms   |
| State:TransactionRollback  | 38.8ms | 39.7ms | 42.4ms | 23.9ms |

Table generated using [benchmark](https://github.com/bavix/laravel-wallet-benchmark/). [Pull Request](https://github.com/bavix/laravel-wallet-benchmark/pull/38).

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
