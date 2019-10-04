# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [4.0.0] - 2019-10-04
### Added
- Added interface `Storeable` for creating your own wallet balance repositories. #103
- Added support for [pcov](https://pecl.php.net/package/pcov), now coated unit tests work in a few seconds, not minutes.
- Allow developers to inherit classes: `Operation`, `Bring`, etc.. #106
- Added personal product discounts (see `Discount` interface).
- Added a separate service for working with the connection. It’s not possible to configure flexibly at the moment, wait for new releases.

### Changed
- The minimum version of php 7.2.
- Old versions of the `laravel/cashier` package have been removed, now support begins with the seventh version.
- New argument `Customer $customer` added to `getAmountProduct` method. #117 @jlstandout
- Now for `LockService` you can choose your own (separate) cache.
- Personal discount information has been added to the `transfers` table. Data is not used in the library, but will be needed for the future.
- New argument `Customer $customer` added to `getTotal` method. #117

### Fixed
- Giving a gift (Santa) no longer goes into minus due to taxes. #111
- Upon receipt, the configuration is marked with default values. #117
- Fixed a bug due to which the wallet could go negative when transferring (exchanging) money, tax. #117
- A more correct balance change, a bug with a successful update in the database and an unsuccessful update of the balance (race condition) field was fixed.
- Fixed a bug with a purchase without funds and tax. When trying to pay, an exception was thrown.
- Reworked and fixed unit tests, fixed bugs.

### Deprecated
- `ProxyService` is deprecated and is no longer used.
- `WalletService::getBalance` method is deprecated, use `Storeable::getBalance`.

## [3.3.0] - 2019-09-10
### Added
- Added the ability to easily overload the main classes #106

## [3.2.1] - 2019-09-10
### Fixed
- Fixed calculation of commission for exchange #101 @haojingliu 
- Update docs #99 @abishekrsrikaanth 

## [3.2.0] - 2019-08-16
### Added
- Race condition problem resolved #82 @kak2z7702 #22 @sidor555 
- Add Code Climate service
- Add support lumen (update docs)

### Changed
- Optimize code
- More unit tests, test fixes

## [3.1.6] - 2019-08-08
### Added
- Add support laravel cashier #87 @imhuso

## [3.1.5] - 2019-08-07
### Fixed 
- Fixed math rounding (mantissa) #85 @anthoz69

## [3.1.4] - 2019-08-03
### Added
- Add support `barryvdh/laravel-ide-helper`

### Fixed 
- Fixed receiving `wallets.transfers` relationship @imhuso

## [3.1.3] - 2019-07-31
### Added
- Add support SQLite on command `RefreshBalance` 
- Add support laravel 6.0
- Add support php 7.4
- Add unit-test's

## [3.1.2] - 2019-07-30
### Added
- Allow to free buy with a negative balance
- Add parameter `$allowZero` to method `canWithdraw`

### Fixed
- method canWithdraw, with a negative price, almost always true

## [3.1.1] - 2019-07-29
### Added 
- Add getCurrencyAttribute
- New unit-test's
- Add docker for php7.4 (need to develop)

### Changed
- Travis CI
- Update README.md

### Removed
- Deprecated interface Taxing

## [3.1.0] - 2019-07-27
### Added
- Add exchange method's.
- Add confirm method's.
- Add method `hasWallet`, sometimes required to verify wallet existence.
- Add currency service (create usd, eur,...).
- Add `MinimalTaxable`.
- Add `Taxable`.
- New exception's.
- Add decimal places (replacement ratio).

### Changed
- Updated dependencies (composer.json).
- New status `exchange`.

### Fixed
- Wallet is not always created. #63 #51 
- Migration mariadb, pgsql, mysql. 
- Fix documentation.
- Optimize code, fasted 1.1x.

### Deprecated
- class `Taxing`.

### Remove
- The ability to change the ratio  `coefficient`.
- Removed private and protected methods, the traits turned out to be more clean.

## [3.0.4] - 2019-07-22
### Fixed
- fixed PostgresSQL 11

## [3.0.3] - 2019-07-06
### Fixed
- Fixed creating a wallet with default slug. #57 @kak2z7702 

## [3.0.2] - 2019-06-18
### Added
- Add support laravel 5.9 (new name 6.0)
- Add support mariadb: 5.5, 10.0+
- Add support percona: 5.6
- Add support mysql: 5.6

## [3.0.1] - 2019-06-17
### Fixed
- The shortened syntax for getting the balance did not work.

## [3.0.0] - 2019-05-25
### Added 
- Add service `CommonService`
- Add service `ProxyService`
- Add service `WalletService`
- Add object Bring (simple transfer)
- Add object Operation (simple transaction)
- Add feature Cart (multi pay + quantity)
- Add method `payFreeCart`
- Add method `safePayCart`
- Add method `payCart`
- Add method `forcePayCart`
- Add method `safeRefundCart`
- Add method `refundCart`
- Add method `forceRefundCart`
- Add method `safeRefundGiftCart`
- Add method `refundGiftCart`
- Add method `forceRefundGiftCart`
- Add method `getUniqueId` to Interface `Product`

### Changed
- applied fixes from cs-fixer
- change singleton path `bavix.wallet::transaction` to `Bavix\Wallet\Models\Transaction::class`
- change singleton path `bavix.wallet::transfer` to `Bavix\Wallet\Models\Transfer::class`
- change singleton path `bavix.wallet::wallet` to `Bavix\Wallet\Models\Wallet::class`
- change method `canBuy`. Added parameter `$quantity`

### Removed
- method `calculateBalance`.
- method `holderTransfers`.
- attribute `$status` from Interfaces/Wallet::transfer
- attribute `$status` from Interfaces/Wallet::safeTransfer
- attribute `$status` from Interfaces/Wallet::forceTransfer
- attribute `$status` from Interfaces/WalletFloat::transfer
- attribute `$status` from Interfaces/WalletFloat::safeTransfer
- attribute `$status` from Interfaces/WalletFloat::forceTransfer
- class `Tax`
- class `WalletProxy`
- protected method `checkAmount`
- protected method `assemble`
- protected method `change`
- protected method `holderTransfers`
- protected method `addBalance`

## [2.4.1] - 2019-05-17
### Fixed
- Readme.md
- lumen framework

### Added
- new tests have been added.
- method `refreshBalance`.

### Deprecated
- method `calculateBalance`.
- method `holderTransfers`.

## [2.4.0] - 2019-05-14
### Added
- Add zh-CN trans. @MoeCasts
- Add ru trans
- Add method `holderTransfers`

### Changed
- optimize `getWallet` method
- optimize relations wallets

### Fixed
- fixed getting a default wallet @MoeCasts

### Removed
- trait CanBePaid (deprecated ^2.2)
- trait CanBePaidFloat (deprecated ^2.2)

## [2.3.2] - 2019-05-13
### Fixed
- patch migrations

## [2.3.1] - 2019-05-13
### Added
- Added require dependency doctrine/dbal in composer.json

## [2.3.0] - 2019-05-13
### Added
- Add support Themosis Framework

### Changed
- In all the methods of translations have added the status of the transfer.

### Fixed
- correction of errors during installation is not correct status.

## [2.2.2] - 2019-05-12
### Fixed 
- fixed fee counting. see issue #25 

## [2.2.1] - 2019-02-17
### Added 
- Add support Laravel 5.8.

## [2.2.0] - 2018-12-25
### Added 
- Add trait `CanPay`.
- Add trait `CanPayFloat`.

### Deprecated
- Trait `CanBePaid`.
- Trait `CanBePaidFloat`.

## [2.1.0] - 2018-11-22
### Added 
- File changelog.
- Add `HasGift` trait.
- Added status column to the `transfers` table.
- Added status_last column to the `transfers` table.
- Added methods: refundGift, safeRefundGift, forceRefundGift
- A new argument is added to the "old `refund`" methods `$gifts`.

### Fixed
- Due to the addition of new functionality `gifts` 
there are possible problems that need to be addressed. 
Namely, when returning the goods, 
the funds would not be returned to 
the person who paid for it. 
Which would raise a lot of questions.

### Changed
- Composer.json: add new keywords.
- the $gifts argument (Boolean type) is added to 
the paid, refund, safeRefund, forceRefund method's.

### Removed
- Removed column `refund` from `transfers` table. 
Now it has been replaced by the status column.

## [2.0.1] - 2018-11-21
### Added 
- add method getAvailableBalance.
public getAvailableBalance(): int.

## [2.0.0] - 2018-11-21
### Added
- table `wallets`.
- add `wallet_id` to table `transactions` and foreign key's.
- add `fee` to table `transfers`.
- add localization's.
- add Taxing interface.
- add WalletFloat interface.
- add const TYPE_DEPOSIT, TYPE_WITHDRAW.
- add Wallet model.
- add working with fractional (float) numbers. 
- add method calculateBalance.
- add method payFree.
public payFree(Product $product): Transfer.
- add CanBePaidFloat trait.
- Added the ability to collect Commission 
when withdrawing funds in transfers.
- Added the ability to work with multiple wallets.
- Added a class that stores user balance. To avoid any problems.
- add HasWalletFloat trait.
- add HasWallets trait.

### Changed
- Add $type argument before $amount. 
protected change(string $type, int $amount, ?array $meta, bool $confirmed): Transaction.

### Fixed
- Due to the addition of the ability to buy for free, 
there was a bug in which we returned the full cost.
- Due to the addition of the ability to work with 
many wallets, there were bugs with payments. 
When the user bought the goods and the goods were 
assigned to the wallet, not to the user.
This change of method: change, transactions, 
transfers, wallet, etc.

## [1.2.3] - 2018-11-11
### Changed
- readme: Added new features.
- Composer.json: add new keywords.

## [1.2.2] - 2018-11-10
### Added 
- method public forcePay(Product $product): Transfer.
- method public forceRefund(Product $product): bool.

### Changed
- the `$force` parameter was added to the `pay` method.
public pay(Product $product, bool $force = false): Transfer.
- the `$force` parameter was added to the `safePay` method.
public safePay(Product $product, bool $force = false): ?Transfer.
- the `$force` parameter was added to the `canBuy` method.
public canBuy(Customer $customer, bool $force = false): bool.
- the `$force` parameter was added to the `refund` method.
public refund(Product $product, bool $force = false): bool.
- the `$force` parameter was added to the `safeRefund` method.
public safeRefund(Product $product, bool $force = false): bool.

### Fixed
- Fixed magic method. 
He accounted for unconfirmed transactions.

## [1.2.1] - 2018-11-09
### Added 
- check for php 7.3

### Fixed
- support for laravel 5.5.

## [1.2.0] - 2018-11-09
### Added 
- phpunit to the project.

## [1.1.2] - 2018-11-08
### Fixed
- Fixed "balance" method. 
He counted the amount along with the unconfirmed transactions.

## [1.1.1] - 2018-11-08
### Changed
- Update readme.
- New indexes have names for quick removal.

### Fixed
- Fixed `down` migration method `transfers`.

## [1.1.0] - 2018-11-08
### Added
- Added index for fields in "transfers" table: ['from_type', 'from_id', 'refund'].
- Added index for fields in "transfers" table: ['to_type', 'to_id', 'refund'].
- In the table "transactions" added to the type index.
- Exception ProductEnded
- Method public paid(Product $product): ?Transfer.
- Method public canBuy(Customer $customer): bool.
- Static balance caching. Also the description for what it is necessary.

### Changed
- In the table "transactions" is deleted the index of the field "uuid" and added a unique index.
- In the table "transfers" is deleted the index of the field "uuid" and added a unique index.
- Method `pay` began to check the possibility of buying.

### Removed
- public resetBalance(): void

## [1.0.0] - 2018-11-07
### Added
- Added `refund` field to `transfers` table.

### Changed
- Updated the `refund` method. 
The operation is now executed in the transaction and updates the new `refund` field.

### Deprecated
- public resetBalance(): void

## [0.0.1] - 2018-11-07
### Added
- written README.
- Project configuration file created.
- Migration 2018_11_06_222923_create_transactions_table.
- Migration 2018_11_07_192923_create_transfers_table.
- `HasWallet` trait and `Wallet` interface.
    - methods:
        - private checkAmount(int $amount): void
        - public forceWithdraw(int $amount, ?array $meta = null, bool $confirmed = true): Transaction
        - public deposit(int $amount, ?array $meta = null, bool $confirmed = true): Transaction
        - public withdraw(int $amount, ?array $meta = null, bool $confirmed = true): Transaction
        - public canWithdraw(int $amount): bool
        - public transfer(Wallet $wallet, int $amount, ?array $meta = null): Transfer
        - public safeTransfer(Wallet $wallet, int $amount, ?array $meta = null): ?Transfer
        - public forceTransfer(Wallet $wallet, int $amount, ?array $meta = null): Transfer
        - protected assemble(Wallet $wallet, Transaction $withdraw, Transaction $deposit): Transfer
        - protected change(int $amount, ?array $meta, bool $confirmed): Transaction
        - public resetBalance(): void
    - relations:
        - public transactions(): MorphMany
        - public transfers(): MorphMany
    - magic property 
        - public getBalanceAttribute(): int
- `CanBePaid` trait and `Product`, `Costomer` interface's
    - methods:
        - public pay(Product $product): Transfer
        - public safePay(Product $product): ?Transfer
        - public refund(Product $product): bool
        - public safeRefund(Product $product): bool
- Exceptions: AmountInvalid, BalanceIsEmpty.
- Models: Transfer, Transaction.

[Unreleased]: https://github.com/bavix/laravel-wallet/compare/4.0.0...HEAD
[4.0.0]: https://github.com/bavix/laravel-wallet/compare/3.3.0...4.0.0
[3.3.0]: https://github.com/bavix/laravel-wallet/compare/3.2.1...3.3.0
[3.2.1]: https://github.com/bavix/laravel-wallet/compare/3.2.0...3.2.1
[3.2.0]: https://github.com/bavix/laravel-wallet/compare/3.1.6...3.2.0
[3.1.6]: https://github.com/bavix/laravel-wallet/compare/3.1.5...3.1.6
[3.1.5]: https://github.com/bavix/laravel-wallet/compare/3.1.4...3.1.5
[3.1.4]: https://github.com/bavix/laravel-wallet/compare/3.1.3...3.1.4
[3.1.3]: https://github.com/bavix/laravel-wallet/compare/3.1.2...3.1.3
[3.1.2]: https://github.com/bavix/laravel-wallet/compare/3.1.1...3.1.2
[3.1.1]: https://github.com/bavix/laravel-wallet/compare/3.1.0...3.1.1
[3.1.0]: https://github.com/bavix/laravel-wallet/compare/3.0.4...3.1.0
[3.0.4]: https://github.com/bavix/laravel-wallet/compare/3.0.3...3.0.4
[3.0.3]: https://github.com/bavix/laravel-wallet/compare/3.0.2...3.0.3
[3.0.2]: https://github.com/bavix/laravel-wallet/compare/3.0.1...3.0.2
[3.0.1]: https://github.com/bavix/laravel-wallet/compare/3.0.0...3.0.1
[3.0.0]: https://github.com/bavix/laravel-wallet/compare/2.4.1...3.0.0
[2.4.1]: https://github.com/bavix/laravel-wallet/compare/2.4.0...2.4.1
[2.4.0]: https://github.com/bavix/laravel-wallet/compare/2.3.2...2.4.0
[2.3.2]: https://github.com/bavix/laravel-wallet/compare/2.3.1...2.3.2
[2.3.1]: https://github.com/bavix/laravel-wallet/compare/2.3.0...2.3.1
[2.3.0]: https://github.com/bavix/laravel-wallet/compare/2.2.2...2.3.0
[2.2.2]: https://github.com/bavix/laravel-wallet/compare/2.2.1...2.2.2
[2.2.1]: https://github.com/bavix/laravel-wallet/compare/2.2.0...2.2.1
[2.2.0]: https://github.com/bavix/laravel-wallet/compare/2.1.0...2.2.0
[2.1.0]: https://github.com/bavix/laravel-wallet/compare/2.0.1...2.1.0
[2.0.1]: https://github.com/bavix/laravel-wallet/compare/2.0.0...2.0.1
[2.0.0]: https://github.com/bavix/laravel-wallet/compare/1.2.3...2.0.0
[1.2.3]: https://github.com/bavix/laravel-wallet/compare/1.2.2...1.2.3
[1.2.2]: https://github.com/bavix/laravel-wallet/compare/1.2.1...1.2.2
[1.2.1]: https://github.com/bavix/laravel-wallet/compare/1.2.0...1.2.1
[1.2.0]: https://github.com/bavix/laravel-wallet/compare/1.1.2...1.2.0
[1.1.2]: https://github.com/bavix/laravel-wallet/compare/1.1.1...1.1.2
[1.1.1]: https://github.com/bavix/laravel-wallet/compare/1.1.0...1.1.1
[1.1.0]: https://github.com/bavix/laravel-wallet/compare/1.0.0...1.1.0
[1.0.0]: https://github.com/bavix/laravel-wallet/compare/0.0.1...1.0.0
[0.0.1]: https://github.com/bavix/laravel-wallet/compare/d181a99e751c5138694580ca4361d5129baa26b3...0.0.1
