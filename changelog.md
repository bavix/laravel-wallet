# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

[Unreleased]: https://github.com/bavix/laravel-wallet/compare/2.3.0...HEAD
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
