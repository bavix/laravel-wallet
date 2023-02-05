# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [9.6.1] - 2023-01-30
### Added
* Add support laravel 10.x

## [9.6.0] - 2022-10-27
### Added
* Full support for standard transactions and laravel nova #589

## [9.5.0] - 2022-10-06
### Added 
* Improved performance api handles #576

### Changed
* Cache query optimize. v2 #580
* Optimize StateServiceInterface #582

### Fixed
* Memory leak. StateServiceInterface #583

## [9.4.0] - 2022-09-29
### Added
* Add support Model::preventSilentlyDiscardingAttributes() #574 #572
* Add partial support octane #573 #570

## [9.3.0] - 2022-09-06
### Added
* StorageServiceLockDecorator by @rez1dent3 in #563
* StateServiceInterface  by @rez1dent3 in #564
* Add atomic-service.md by @rez1dent3 in #561

### Updated
* Bump uuid from 8.3.2 to 9.0.0 by @dependabot in #566

## [9.2.0] - 2022-09-04
### Updated
- upgrade actions by @rez1dent3 in #541
- Bump size-limit from 8.0.0 to 8.0.1 by @dependabot in #543
- Update rector/rector requirement from ^0.13 to ^0.14 by @dependabot in #544
- Update laravel/cashier requirement from ^13.11 to ^14.0 by @dependabot in #545
- Update linters and rules by @rez1dent3 in #547
- Bump prismjs from 1.28.0 to 1.29.0 by @dependabot in #549

### Fixed
- Contract phpdoc fix by @rez1dent3 in #551
- fix TestCase by @rez1dent3 in #555

### Added
- Add telegram link by @rez1dent3 in #553
- Ability to dynamically create a wallet by @rez1dent3 in #550
- Quality tests by @rez1dent3 in #554
- Allow to use atomic service by @rez1dent3 in #548
- docs by @rez1dent3 in #557

### Changed
- refactoring with new phpstan by @rez1dent3 in #556

## [9.1.0] - 2022-08-08
### Added
- TransactionCreatedEvent #538

### Fixed
- Fixed a bug with sending multiple events inside the queue. Extra events were sent.

## [9.0.4] - 2022-07-28
### Fixed
- Add allow plugin infection by @rez1dent3 in #528
- Fix transaction amount_float mutator by @keatliang2005 in #533 #534

### Updated
- Bump terser from 5.13.0 to 5.14.2 by @dependabot in #527
- Bump webpack from 5.73.0 to 5.74.0 by @dependabot in #529

## [9.0.3] - 2022-06-22
### Fixed
- Fixed lumen. Change CacheManager to CacheFactory for compatibility. #520 #521 @Beagon 

## [9.0.2] - 2022-06-18
### Fixed
- Fix laravel-ide-helper generate:model #517 @keatliang2005 

## [9.0.1] - 2022-05-19
### Fixed
- Fixed a bug that prevented items from being returned via `Cart::withItem`

## [9.0.0] - 2022-05-02 [#481](https://github.com/bavix/laravel-wallet/pull/481)
### Added
- ExtraDtoInterface #479
- Product custom price #485

### Changed
- Changing the logic of funds transfers #483
- Split Product interface #474
- PHP 8+ Union types #482
- Eager loading #480

### Removed
- method `Cart::addItems`
- method `Cart::addItem`
- method `Cart::setMeta`

### Updated
- Performance just got a little better
- Public contracts have become stricter
- Inside is now strongly typed

### Deprecated
- interface `Product`
- method `CartPay::paid`

## [8.4.3] - 2023-02-05
### Fixed
- Fixed infinite lock

## [8.4.2] - 2022-12-29
### Fixed
- fix withdraw/transfer

## [8.4.1] - 2022-04-26
### Deprecated
- Cart::getUniqueItems

## [8.4.0] - 2022-04-20
### Added
- MaximalTaxable
- Added strictness in internal classes

## [8.3.0] - 2022-04-15
### Added
- Added the ability to create custom events

### Removed
- UnknownEventException

### Updated
- Reduced the amount of memory consumed in the cart
- Improved product returns performance

## [8.2.1] - 2022-04-14
### Fixed
- Error in force transaction float #469 @EdX9

## [8.2.0] - 2022-04-03
### Added
- Add exception `TransactionStartException`

## [8.1.1] - 2022-03-18
### Updated
- Expanding the error description. Helps to reduce the number of issue.

## [8.1.0] - 2022-03-13
### Removed
- Method `getAvailableBalance`.

### Added
- Methods `withItems`, `withItem`, `withMeta` on Cart-object.

### Deprecated
- Method `addItems`, `addItem`, `setMeta` on Cart-Object.

## [8.0.6] - 2022-02-26
### Updated
- Replaced an object with an interface (Events) #444 @ysfkaya

## [8.0.5] - 2022-02-21
### Added
- Spanish language #443 @EdX9

## [8.0.4] - 2022-02-19
### Added
- Added validation when resetting confirmation. Caused unexpected system behavior.

## [8.0.3] - 2022-02-19
### Fixed
- Fixed "UUID Duplicate entry" bug on eager loading. #438 @DanielSpravtsev
- Use predefined PHP float epsilon (phpunit) sebastianbergmann/phpunit#4874

## [8.0.2] - 2022-02-12
### Fixed
- Added keys to service provider

## [8.0.1] - 2022-02-12
### Fixed
- Fixed bug preventing redis from being used #429 @mattvb91

## [8.0.0] - 2022-02-08
### Added
- Add support laravel ^9.0
- Added support for owner string identifiers #423 @adesege

### Removed
- Removed php 7.4 support

## [7.3.6] - 2023-02-05
### Fixed
- Fixed infinite lock

## [7.3.5] - 2022-12-29
### Fixed
- fix withdraw/transfer

## [7.3.4] - 2022-08-08
### Fixed
- Fixed a bug with sending multiple events inside the queue. Extra events were sent.

## [7.3.3] - 2022-02-26
### Updated
- Replaced an object with an interface (Events) #444 @ysfkaya

## [7.3.2] - 2022-02-12
### Fixed
- Added keys to service provider

## [7.3.1] - 2022-02-12
### Fixed
- Fixed bug preventing redis from being used #429 @mattvb91

## [7.3.0] - 2021-12-10
### Added
- Credit Limits
- WalletCreatedEvent

### Updated
- Optimization of check for withdrawals;

## [7.2.0] - 2021-12-07
### Added
- Added balance update event

## [7.1.0] - 2021-12-05
### Added
- Transaction support.
- Now, within the transaction, the wallet has its own balance state.

### Fixed
- Fixed unit tests with checking for transaction level (mariadb).

### Updated
- Due to the state within transactions, I was able to speed up the computation up to 25 times for complex transfers.

### Removed
- class `WalletServiceLegacy`
- method `CommonServiceLegacy::addBalance`

## [7.0.0] - 2021-11-25
### Updated
- Optimization of the `payFreeCart` and `payFree` request. Now the package does not update the repository. But there is no point in updating it, because the client does not pay anything.
- Now everything is in contracts. It became easier for you to modify the package to suit your needs.
- Updated package core. If you are tied to the kernel, then you will have to rewrite some code.
- Optimized the algorithm for transfers and purchases. When paying for a large basket, the productivity increase at the peak is up to 24 times.
- If a batch of transactions does not change the balance, then the accounting service will not update the wallet balance.

### Fixed
- Fixed issues with postgres. There was a bug when working with currencies, for some reason the request sometimes dropped and went into a deadlock.

### Added
- Added `uuid` column to the wallet table.
- Added `phpstan`, `psalm`, `deptrac`, `rector`. The package update should now be smoother and with fewer bugs.

### Renamed
- rename `CommonService` to `CommonServiceLegacy`
- rename `MetaService` to `MetaServiceLegacy`
- rename `WalletService` to `WalletServiceLegacy`

### Deprecated
- class `CommonServiceLegacy`
- class `WalletServiceLegacy`

### Removed
- command `RefreshBalance`. Now you need to write this class yourself.
- class `Storable`
- class `Rateable`
- interface `Mathable`
- class `Bring`
- method `Cart::alreadyBuy`
- method `Cart::canBuy`
- class `EmptyLock`
- class `Operation`
- method `CommonService::verifyWithdraw`
- method `CommonService::multiOperation`
- method `CommonService::assemble`
- method `CommonService::multiBrings`
- class `DbService`
- class `ExchangeService`
- class `LockService`
- method `WalletService::discount`
- method `WalletService::decimalPlacesValue`
- method `WalletService::decimalPlaces`
- method `WalletService::checkAmount`
- method `WalletService::adjustment`
- class `BrickMath`
- class `Rate`
- class `Store`

## [6.2.4] - 2021-11-13
### Fixed
- Fixed error LockProviderNotFoundException

## [6.2.3] - 2021-11-08
### Fixed
- Fixed a bug with `migrate:refresh`

## [6.2.2] - 2021-11-02
### Changed
- Update key from cache

## [6.2.1] - 2021-11-02
### Fixed
- Fix looping for old configs. #387 @AbdullahFaqeir, #391 @Hussam3bd @alexstewartja

## [6.2.0] - 2021-10-29
### Added
- ECS
- Added new exception `UnconfirmedInvalid`

### Changed
- Raised the minimum php version `7.4+`
- Merged migrations

### Deprecated
- class `Storable`
- class `Rateable`
- interface `Mathable`
- class `Bring`
- method `Cart::alreadyBuy`
- method `Cart::canBuy`
- class `EmptyLock`
- class `Operation`
- method `CommonService::verifyWithdraw`
- method `CommonService::multiOperation`
- method `CommonService::assemble`
- method `CommonService::multiBrings`
- method `CommonService::addBalance`
- class `DbService`
- class `ExchangeService`
- class `LockService`
- class `MetaService`
- method `WalletService::discount`
- method `WalletService::decimalPlacesValue`
- method `WalletService::decimalPlaces`
- method `WalletService::checkAmount`
- method `WalletService::refresh`
- method `WalletService::adjustment`
- class `BrickMath`
- class `Rate`
- class `Store`

## [6.1.0] - 2021-04-18
### Added
- Added Github Actions
- Add farsi locale; #317 #320 @hsharghi
- Added the ability to add meta data from the cart #318
- Added exceptions to phpdoc

### Changed
- Reworked unit tests
- Unit tests work faster

### Fixed
- Fixed a bug in the calculation of the commission

## [6.0.4] - 2021-04-07
### Fixed
- Updated key `confirmed_invalid` in Arabic; #316 @omarhen 

## [6.0.3] - 2021-01-31
### Added
- Add arabic locale; #302 @akhedrane

## [6.0.2] - 2020-11-28
### Added
- Added `getWalletOrFail` method.

## [6.0.1] - 2020-11-18
### Fixed
- Fixed a bug when updating the balance, refund and full write-off. #279 @vaibhavpandeyvpz
- Fixed bugs in unit tests.

## [6.0.0] - 2020-11-13
### Added
- Bigger and safer. There are never many tests. As always, new test cases have been added.
- Package `brick/math` is now required.
- Added examples of integrations with the `cknow/laravel-money` package in unit tests.
- The `Storable` interface has an additional method `fresh` to clean up all data.
- Added psalm, but not used yet.
- Added phpmetrics, thanks to which it was possible to remove a lot of loops in the code.
- Added meta column in wallet, now the package is more extensible. I moved currency from the config to the meta.
- Added an icon to the documentation.
- Added full support for php 8. We are waiting for the release.
- Added `adjustment` method, it deals with balance adjustment. In automatic mode, it calculates the difference between the current amount on the balance sheet and for transactions, and if the balance does not converge, then finishes with a transaction.
- Added the ability to initialize the default wallet with the required meta parameters (needed to work with currencies).
- Added method `negative` to `Mathable` interface.

### Changed
- Now the package works exclusively with strings, there are fewer problems when working with large numbers.
- Now, to work with cryptocurrencies, it is not necessary to install `bcmath`.
- JS documentation is no longer dependent on CDN, everything is collected in one bundle.
- Updated the command to refresh the balance, now without a transaction for all wallets. Use carefully.
- Currencies are now in "wallets.meta.currency", please do not use the config for these cases.
- English documentation has been slightly improved.
- Updated phpunit to support php 8.

### Removed
- Removed php 7.2 support.
- Drop package `laravel/legacy-factories`.
- Remove `BCMath` and `Math` classes.

### Fixed
- Fixed a bug when withdrawing large funds from the wallet. Sometimes the number went beyond int64 and the exception fell on "negative number".

### Deprecated
- The key in the currencies config will be removed in 7.x. Use "wallet.meta.currency".

## [5.5.1] - 2020-10-18
### Fixed
- Fixed a bug when updating the balance, refund and full write-off. #279 @vaibhavpandeyvpz
- Fixed bugs in unit tests.

## [5.5.0] - 2020-10-01
### Added
- Added brick/math dependency (Optional in version 5.x. If you already have a package installed, the library will switch to it automatically)
- Added more php annotations, now it's easier to work with the library.
- Updated travis. Now we check not only SQLite, but also mysql & postgres.

### Fixed
- Fixed memory leak in models.

### Deprecated
- class `BCMath`.
- class `Math`.

## [5.4.0] - 2020-09-30
### Added
- Temporarily added package (to develop) laravel/legacy-factories.

### Changed
- PHP 7.3+ support, 7.2 is no longer supported.
- Formatted code using StyleCI.
- The mysql/postgres balance refresh command no longer performs a single request update.
- If you use standard laravel transactions and open it, the library will not open a new transaction. 
This removes a lot of errors that were sent to my email.
- Removed automatic creation of the default wallet when calling `createWallet`. #218

### Fixed
- Fixed migrations for unit tests (your app should not be affected).
- Fixed nested transactions in databases. This is now one transaction.
- Fixed risk in unit tests for the postgres database.

## [5.3.2] - 2020-08-31
### Added
- Add support laravel ~8.0
- Dependency Allowed `illuminate/database` ^8.0 
- Dependency Allowed `doctrine/dbal` ^3.0
- Dependency Allowed `infection/infection` 0.17.*
- Added new unit tests

## [5.3.1] - 2020-08-18
### Fixed
- Fixed migration issue with db table prefix #195 @reedknight @cispl-shaswatad

## [5.3.0] - 2020-08-10
### Added
- Add `resetConfirm`, `safeResetConfirm` methods (unconfirmed).
- Allow default migrations to be ignored. #189 @driangonzales 

## [5.2.1] - 2020-06-10
### Added
- Added support `laravel/cashier ^12.0`

## [5.2.0] - 2020-04-15
### Added
- Added support `laravel/cashier ^11.0`

## [5.1.0] - 2020-03-26
### Added
- Added support `ramsey/uuid ^4.0`

### Fixed
- pg12 support

## [5.0.2] - 2020-03-22
### Fixed
- fix `bindTo` method (v4.1)

## [5.0.1] - 2020-03-19
### Added
- Added a patch from version 4.2.1 #150

## [5.0.0] - 2020-03-13

### Added
- add support "Arbitrary Precision Mathematics" (`ext-bcmath`) #139 #146
- add `Mathable` service (helps switch quickly from bcmath to php computing)

### Changed
- add unit cases
- upgrade composer packages
- Now all casts are in the config, not in the model. If you use bcmath, then all values are reduced to a string.

### Removed
- Strong typing (models, interfaces, etc.)
- all deprecated methods are removed
- `nesbot/carbon` is no longer needed for the library to work

## [4.2.2] - 2020-03-22
### Fixed
- fix `bindTo` method (v4.1)

## [4.2.1] - 2020-03-19
### Fixed 
- Fixed wallet recalculate command #150

## [4.2.0] - 2020-03-08

### Added
- Add laravel 7 support

## [4.1.4] - 2020-03-22
### Fixed
- fix `bindTo` method

## [4.1.3] - 2020-03-20
### Added
- Added a patch from version 4.2.1 #150

## [4.1.2] - 2020-01-20
### Added
- add `$amountFloat` to Transaction model

## [4.1.1] - 2020-01-16
### Changed
- upgrade composer packages
- add unit cases

## [4.1.0] - 2019-12-15
### Added
- Added ability to override type

## [4.0.1] - 2019-11-30
### Fixed
- Encountered error: "You are not the owner of the wallet" #129 @arjayosma

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

317
### Deprecated
318
- method `calculateBalance`.
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

### Removed
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

[Unreleased]: https://github.com/bavix/laravel-wallet/compare/9.6.1...10.x
[9.6.1]: https://github.com/bavix/laravel-wallet/compare/9.6.0...9.6.1
[9.6.0]: https://github.com/bavix/laravel-wallet/compare/9.5.0...9.6.0
[9.5.0]: https://github.com/bavix/laravel-wallet/compare/9.4.0...9.5.0
[9.4.0]: https://github.com/bavix/laravel-wallet/compare/9.3.0...9.4.0
[9.3.0]: https://github.com/bavix/laravel-wallet/compare/9.2.0...9.3.0
[9.2.0]: https://github.com/bavix/laravel-wallet/compare/9.1.0...9.2.0
[9.1.0]: https://github.com/bavix/laravel-wallet/compare/9.0.4...9.1.0
[9.0.4]: https://github.com/bavix/laravel-wallet/compare/9.0.3...9.0.4
[9.0.3]: https://github.com/bavix/laravel-wallet/compare/9.0.2...9.0.3
[9.0.2]: https://github.com/bavix/laravel-wallet/compare/9.0.1...9.0.2
[9.0.1]: https://github.com/bavix/laravel-wallet/compare/9.0.0...9.0.1
[9.0.0]: https://github.com/bavix/laravel-wallet/compare/8.4.3...9.0.0
[8.4.3]: https://github.com/bavix/laravel-wallet/compare/8.4.2...8.4.3
[8.4.2]: https://github.com/bavix/laravel-wallet/compare/8.4.1...8.4.2
[8.4.1]: https://github.com/bavix/laravel-wallet/compare/8.4.0...8.4.1
[8.4.0]: https://github.com/bavix/laravel-wallet/compare/8.3.0...8.4.0
[8.3.0]: https://github.com/bavix/laravel-wallet/compare/8.2.1...8.3.0
[8.2.1]: https://github.com/bavix/laravel-wallet/compare/8.2.0...8.2.1
[8.2.0]: https://github.com/bavix/laravel-wallet/compare/8.1.1...8.2.0
[8.1.1]: https://github.com/bavix/laravel-wallet/compare/8.1.0...8.1.1
[8.1.0]: https://github.com/bavix/laravel-wallet/compare/8.0.6...8.1.0
[8.0.6]: https://github.com/bavix/laravel-wallet/compare/8.0.5...8.0.6
[8.0.5]: https://github.com/bavix/laravel-wallet/compare/8.0.4...8.0.5
[8.0.4]: https://github.com/bavix/laravel-wallet/compare/8.0.3...8.0.4
[8.0.3]: https://github.com/bavix/laravel-wallet/compare/8.0.2...8.0.3
[8.0.2]: https://github.com/bavix/laravel-wallet/compare/8.0.1...8.0.2
[8.0.1]: https://github.com/bavix/laravel-wallet/compare/8.0.0...8.0.1
[8.0.0]: https://github.com/bavix/laravel-wallet/compare/7.3.6...8.0.0
[7.3.6]: https://github.com/bavix/laravel-wallet/compare/7.3.5...7.3.6
[7.3.5]: https://github.com/bavix/laravel-wallet/compare/7.3.4...7.3.5
[7.3.4]: https://github.com/bavix/laravel-wallet/compare/7.3.3...7.3.4
[7.3.3]: https://github.com/bavix/laravel-wallet/compare/7.3.2...7.3.3
[7.3.2]: https://github.com/bavix/laravel-wallet/compare/7.3.1...7.3.2
[7.3.1]: https://github.com/bavix/laravel-wallet/compare/7.3.0...7.3.1
[7.3.0]: https://github.com/bavix/laravel-wallet/compare/7.2.0...7.3.0
[7.2.0]: https://github.com/bavix/laravel-wallet/compare/7.1.0...7.2.0
[7.1.0]: https://github.com/bavix/laravel-wallet/compare/7.0.0...7.1.0
[7.0.0]: https://github.com/bavix/laravel-wallet/compare/6.2.4...7.0.0
[6.2.4]: https://github.com/bavix/laravel-wallet/compare/6.2.3...6.2.4
[6.2.3]: https://github.com/bavix/laravel-wallet/compare/6.2.2...6.2.3
[6.2.2]: https://github.com/bavix/laravel-wallet/compare/6.2.1...6.2.2
[6.2.1]: https://github.com/bavix/laravel-wallet/compare/6.2.0...6.2.1
[6.2.0]: https://github.com/bavix/laravel-wallet/compare/6.1.0...6.2.0
[6.1.0]: https://github.com/bavix/laravel-wallet/compare/6.0.4...6.1.0
[6.0.4]: https://github.com/bavix/laravel-wallet/compare/6.0.3...6.0.4
[6.0.3]: https://github.com/bavix/laravel-wallet/compare/6.0.2...6.0.3
[6.0.2]: https://github.com/bavix/laravel-wallet/compare/6.0.1...6.0.2
[6.0.1]: https://github.com/bavix/laravel-wallet/compare/6.0.0...6.0.1
[6.0.0]: https://github.com/bavix/laravel-wallet/compare/5.5.1...6.0.0
[5.5.1]: https://github.com/bavix/laravel-wallet/compare/5.5.0...5.5.1
[5.5.0]: https://github.com/bavix/laravel-wallet/compare/5.4.0...5.5.0
[5.4.0]: https://github.com/bavix/laravel-wallet/compare/5.3.2...5.4.0
[5.3.2]: https://github.com/bavix/laravel-wallet/compare/5.3.1...5.3.2
[5.3.1]: https://github.com/bavix/laravel-wallet/compare/5.3.0...5.3.1
[5.3.0]: https://github.com/bavix/laravel-wallet/compare/5.2.1...5.3.0
[5.2.1]: https://github.com/bavix/laravel-wallet/compare/5.2.0...5.2.1
[5.2.0]: https://github.com/bavix/laravel-wallet/compare/5.1.0...5.2.0
[5.1.0]: https://github.com/bavix/laravel-wallet/compare/5.0.2...5.1.0
[5.0.2]: https://github.com/bavix/laravel-wallet/compare/5.0.1...5.0.2
[5.0.1]: https://github.com/bavix/laravel-wallet/compare/5.0.0...5.0.1
[5.0.0]: https://github.com/bavix/laravel-wallet/compare/4.2.2...5.0.0
[4.2.2]: https://github.com/bavix/laravel-wallet/compare/4.2.1...4.2.2
[4.2.1]: https://github.com/bavix/laravel-wallet/compare/4.2.0...4.2.1
[4.2.0]: https://github.com/bavix/laravel-wallet/compare/4.1.4...4.2.0
[4.1.4]: https://github.com/bavix/laravel-wallet/compare/4.1.3...4.1.4
[4.1.3]: https://github.com/bavix/laravel-wallet/compare/4.1.2...4.1.3
[4.1.2]: https://github.com/bavix/laravel-wallet/compare/4.1.1...4.1.2
[4.1.1]: https://github.com/bavix/laravel-wallet/compare/4.1.0...4.1.1
[4.1.0]: https://github.com/bavix/laravel-wallet/compare/4.0.1...4.1.0
[4.0.1]: https://github.com/bavix/laravel-wallet/compare/4.0.0...4.0.1
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
