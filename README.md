![Laravel Wallet](https://github.com/bavix/laravel-wallet/assets/5111255/95e7877c-a950-4b04-9414-de62216d31c2)

[![Maintainability](https://api.codeclimate.com/v1/badges/588400f5f40cbbf3a8ab/maintainability)](https://codeclimate.com/github/bavix/laravel-wallet/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/588400f5f40cbbf3a8ab/test_coverage)](https://codeclimate.com/github/bavix/laravel-wallet/test_coverage) [![Financial Contributors on Open Collective](https://opencollective.com/laravel-wallet/all/badge.svg?label=financial+contributors)](https://opencollective.com/laravel-wallet) [![Mutation testing badge](https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fbavix%2Flaravel-wallet%2Fmaster)](https://packagist.org/packages/bavix/laravel-wallet)

[![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v)](https://packagist.org/packages/bavix/laravel-wallet) [![Latest Unstable Version](https://poser.pugx.org/bavix/laravel-wallet/v/unstable)](https://packagist.org/packages/bavix/laravel-wallet) [![License](https://poser.pugx.org/bavix/laravel-wallet/license)](https://packagist.org/packages/bavix/laravel-wallet) [![composer.lock](https://poser.pugx.org/bavix/laravel-wallet/composerlock)](https://packagist.org/packages/bavix/laravel-wallet)

[![Sparkline](https://stars.medv.io/bavix/laravel-wallet.svg)](https://stars.medv.io/bavix/laravel-wallet)

laravel-wallet - It's easy to work with a virtual wallet.

[[Benchmark](https://github.com/bavix/laravel-wallet-benchmark/)] 
[[Documentation](https://bavix.github.io/laravel-wallet/)] 
[[Get Started](https://bavix.github.io/laravel-wallet/guide/introduction/)] 

* **Vendor**: bavix
* **Package**: laravel-wallet
* **[Composer](https://getcomposer.org/):** `composer require bavix/laravel-wallet`

### Support Policy

| Version    | Laravel        | PHP             | Release date | End of improvements | End of support |
|------------|----------------|-----------------|--------------|---------------------|----------------|
| 11.x [LTS] | ^11.0, ^12.0   | 8.2,8.3,8.4     | Mar 14, 2024 | May 1, 2026         | Sep 6, 2026    |

### Upgrade Guide

To perform the migration, you will be [helped by the instruction](https://bavix.github.io/laravel-wallet/#/upgrade-guide).

### Community

I want to create a cozy place for developers using the wallet package. This will help you find bugs faster, get feedback and discuss ideas.

![telegram](https://github.com/bavix/laravel-wallet/assets/5111255/ed2b1193-c0c6-41af-83cb-0fe61ae8df21)


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
|            Name            |  7.3   |  8.4   |  9.6   |  10.1  |  11.0  |
|----------------------------|--------|--------|--------|--------|--------|
| Atomic:Blocks              | -      | -      | 484ms  | 493ms  | 493ms  |
| Cart:EagerLoaderPay        | 22s    | 679ms  | 493ms  | 530ms  | 652ms  |
| Cart:Pay                   | 1.36s  | 472ms  | 288ms  | 298ms  | 336ms  |
| Cart:PayFree               | 1.3s   | 415ms  | 281ms  | 291ms  | 287ms  |
| Cart:PayOneItemXPieces     | 565ms  | 118ms  | 59.1ms | 64.6ms | 66.2ms |
| Gift:Gift                  | 44.8ms | 53.5ms | 54.3ms | 58.4ms | 64.3ms |
| Gift:Refund                | 106ms  | 112ms  | 108ms  | 111ms  | 139ms  |
| Solo:Deposit               | 27.4ms | 31.8ms | 31ms   | 33.3ms | 30.1ms |
| Solo:EagerLoading          | 904ms  | 1.09s  | 876ms  | 927ms  | 1.02s  |
| Solo:ForceWithdraw         | 27.6ms | 31.8ms | 30.7ms | 32.9ms | 30ms   |
| Solo:GetBalance            | 20.8ms | 24ms   | 23.7ms | 23.4ms | 20ms   |
| Solo:Transfer              | 39.4ms | 45.7ms | 42ms   | 44.9ms | 46.6ms |
| Solo:Withdraw              | 31.1ms | 36.3ms | 34.9ms | 37.3ms | 37.8ms |
| State:InTransaction        | 570ms  | 566ms  | 419ms  | 425ms  | 427ms  |
| State:RefreshInTransaction | 32.3ms | 41.2ms | 41.2ms | 45.6ms | 47.2ms |
| State:TransactionRollback  | 29.7ms | 34.1ms | 32.9ms | 37.2ms | 36.9ms |

Table generated using [benchmark](https://github.com/bavix/laravel-wallet-benchmark/). [Pull Request](https://github.com/bavix/laravel-wallet-benchmark/pull/51).

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
