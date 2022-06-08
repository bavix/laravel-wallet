![Laravel Wallet](https://user-images.githubusercontent.com/5111255/48687709-a7c2fa00-ebd3-11e8-8714-c4f3efe93f02.png)

[![Maintainability](https://api.codeclimate.com/v1/badges/588400f5f40cbbf3a8ab/maintainability)](https://codeclimate.com/github/bavix/laravel-wallet/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/588400f5f40cbbf3a8ab/test_coverage)](https://codeclimate.com/github/bavix/laravel-wallet/test_coverage) [![Financial Contributors on Open Collective](https://opencollective.com/laravel-wallet/all/badge.svg?label=financial+contributors)](https://opencollective.com/laravel-wallet) [![Mutation testing badge](https://badge.stryker-mutator.io/github.com/bavix/laravel-wallet/master)](https://packagist.org/packages/bavix/laravel-wallet)

[![Package Rank](https://phppackages.org/p/bavix/laravel-wallet/badge/rank.svg)](https://packagist.org/packages/bavix/laravel-wallet) [![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v)](https://packagist.org/packages/bavix/laravel-wallet) [![Latest Unstable Version](https://poser.pugx.org/bavix/laravel-wallet/v/unstable)](https://packagist.org/packages/bavix/laravel-wallet) [![License](https://poser.pugx.org/bavix/laravel-wallet/license)](https://packagist.org/packages/bavix/laravel-wallet) [![composer.lock](https://poser.pugx.org/bavix/laravel-wallet/composerlock)](https://packagist.org/packages/bavix/laravel-wallet)

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

|          Name          |    6.x.x    |    7.x.x    |    8.x.x    |    9.x.x    |
|------------------------|-------------|-------------|-------------|-------------|
| Cart:EagerLoaderPay    | 58.949697s  | 49.58581s   | 1.466579s   | 1.032154s   |
| Cart:Pay               | 4.08699296s | 2.50341516s | 1.03995896s | 721.67092ms |
| Cart:PayFree           | 4.15599512s | 2.36554972s | 1.01579044s | 631.9784ms  |
| Cart:PayOneItemXPieces | 2.0559186s  | 1.01231272s | 252.7258ms  | 83.73056ms  |
| Solo:Deposit           | 38.18582ms  | 36.67559ms  | 37.35399ms  | 35.68319ms  |
| Solo:EagerLoading      | 2.13623892s | 8.82851804s | 2.30754092s | 2.14806152s |
| Solo:ForceWithdraw     | 38.39115ms  | 36.87102ms  | 37.52448ms  | 35.47786ms  |
| Solo:GetBalance        | 23.543576ms | 24.199633ms | 23.38236ms  | 23.888203ms |
| Solo:Transfer          | 78.80488ms  | 72.01921ms  | 72.81176ms  | 59.42262ms  |
| State:InTransaction    | 1.82724428s | 766.2884ms  | 771.08604ms | 767.08376ms |

Table generated using [benchmark](https://github.com/bavix/laravel-wallet-benchmark/).

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
