# laravel-wallet

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/bavix/laravel-wallet/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/bavix/laravel-wallet/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/build.png?b=master)](https://scrutinizer-ci.com/g/bavix/laravel-wallet/build-status/master)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/bavix/laravel-wallet/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)

[![Package Rank](https://phppackages.org/p/bavix/laravel-wallet/badge/rank.svg)](https://packagist.org/packages/bavix/laravel-wallet)
[![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v/stable)](https://packagist.org/packages/bavix/laravel-wallet)
[![Latest Unstable Version](https://poser.pugx.org/bavix/laravel-wallet/v/unstable)](https://packagist.org/packages/bavix/laravel-wallet)
[![License](https://poser.pugx.org/bavix/laravel-wallet/license)](https://packagist.org/packages/bavix/laravel-wallet)
[![composer.lock](https://poser.pugx.org/bavix/laravel-wallet/composerlock)](https://packagist.org/packages/bavix/laravel-wallet)

laravel-wallet - Easy work with virtual wallet.

* **Vendor**: bavix
* **Package**: laravel-wallet
* **Version**: [![Latest Stable Version](https://poser.pugx.org/bavix/laravel-wallet/v/stable)](https://packagist.org/packages/bavix/laravel-wallet)
* **PHP Version**: 7.1+ 
* **[Composer](https://getcomposer.org/):** `composer require bavix/laravel-wallet`

### Run Migrations
Publish the migrations with this artisan command:
```
php artisan vendor:publish --provider="Depsimon\Wallet\WalletServiceProvider" --tag=migrations
```

### Configuration
You can publish the config file with this artisan command:
```
php artisan vendor:publish --provider="Depsimon\Wallet\WalletServiceProvider" --tag=config
```

### Usage
Add the HasWallet trait to model.
```
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet;
}
```

Now we make transactions.

```
$user = User::first();
$user->balance; // int(0)

$user->deposit(10);
$user->balance; // int(10)

$user->withdraw(1);
$user->balance; // int(9)

$user->forceWithdraw(200);
$user->balance; // -191
```

---
Supported by

[![Supported by JetBrains](https://cdn.rawgit.com/bavix/development-through/46475b4b/jetbrains.svg)](https://www.jetbrains.com/)
