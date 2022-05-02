# Upgrade Guide

## 1.x.x → 2.x.x

Replace `::with('balance')` to `::with('wallet')`

---

## 2.1.x → 2.2.x

Replace `CanBePaid` to `CanPay`.

Replace `CanBePaidFloat` to `CanPayFloat`.

---

## 2.2.x → 2.4.x

Replace `calculateBalance` to `refreshBalance`

---

## 2.4.x → 3.0.x

Replace path `bavix.wallet::transaction` to `Bavix\Wallet\Models\Transaction::class`

Replace path `bavix.wallet::transfer` to `Bavix\Wallet\Models\Transfer::class`

Replace path `bavix.wallet::wallet` to `Bavix\Wallet\Models\Wallet::class`

```php
// old
app('bavix.wallet::transaction'); 
// new
app(Bavix\Wallet\Models\Transaction::class); 
```

Add the `$quantity` parameter to the `canBuy` method.

```php
// old
public function canBuy(Customer $customer, bool $force = false): bool
// new
public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
```

Add method `getUniqueId` to Interface `Product`

```php
class Item extends Model implements Product
{
    
    // Your method
    
    public function getUniqueId(): string
    {
        return (string)$this->getKey();
    }
    
}
```

## 3.0.x → 3.1.x

Replace `Taxing` to `Taxable`.

## 3.1.x → 4.0.x

> If you are using php 7.1, then version 4.0 is not available to you. 
> You need to update php.

Removed support for older versions of `laravel/cashier`. We support 7+.

#### If you use payment for goods

You must add the argument `Customer $customer` to the `getAmountProduct` 
method of your model.

Your code on 3.x:
```php
    public function getAmountProduct(): int
    {
        return $this->price;
    }
```

Your code on 4.x:
```php
    public function getAmountProduct(Customer $customer): int
    {
        return $this->price;
    }
```

## 4.0.x → 5.0.x

> By updating the library from 4.x to 5.x you lose strong typing. 
> This solution was necessary to support APM (Arbitrary Precision Mathematics).

In your goods:

Your code on 4.x:
```php
    public function getAmountProduct(Customer $customer): int  { ... }

    public function getFeePercent(): float  { ... }

    public function getMinimalFee(): int { ... }
```

Your code on 5.x:
```php
    public function getAmountProduct(Customer $customer) { ... }

    public function getFeePercent() { ... }

    public function getMinimalFee() { ... }
```

In the exchange rate processing service:

Your code on 4.x:
```php
    protected function rate(Wallet $wallet): float { ... }

    public function convertTo(Wallet $wallet): float { ... }
```

Your code on 5.x:
```php
    protected function rate(Wallet $wallet) { ... }

    public function convertTo(Wallet $wallet) { ... }
```

## 5.x.x → 6.0.x

Go to `config/wallet.php` file (if you have it) and edit it.

*Removing unnecessary code.*
```php
$bcLoaded = extension_loaded('bcmath');	
$mathClass = Math::class;	
switch (true) {	
    case class_exists(BigDecimal::class):	
        $mathClass = BrickMath::class;	
        break;	
    case $bcLoaded:	
        $mathClass = BCMath::class;	
        break;	
}	
```

*Replace your math class ($mathClass) with `brick/math`.*

Your code on 5.x:
```php
    'mathable' => $mathClass,
```

Your code on 6.x:
```php
    'mathable' => BrickMath::class,
```

## 6.x.x → 7.x.x

*Update `config/wallet.php`*

The `config/wallet.php` config has changed a lot, if you have it in your project, then replace it run.

```bash
php artisan vendor:publish --tag=laravel-wallet-config --force
```

Then return your settings. The package configuration has changed globally and there is no point in describing each key 🔑

---

*UUID for wallet*

The uuid field has been added to the wallet table, which is now actively used.
If you have a highload, then I recommend that you add the field yourself and mark the migration (UpdateWalletsUuidTable) completed.
If you have mysql, it is better to do this via [pt-online-schema-change](https://www.percona.com/doc/percona-toolkit/3.0/pt-online-schema-change.html).

If you have a small project and a small wallet base, then the migration will be applied automatically.

---

That's it, you can use all 7.x functions to the fullest. 
The contract did not change globally, added more stringency and toned down the performance of the package. 
On a basket of 150 products, the acceleration is a whopping 24x.

All changes can be found in the [pull request](https://github.com/bavix/laravel-wallet/pull/407/files). 
The kernel has changed globally, I would not recommend switching to version 7.0.0 at the very beginning, there may be bugs. 
I advise you should at least 7.0.1.

## 7.x.x → 8.0.x

Nothing needs to be done.

## 8.0.x → 8.1.x

Replace `getAvailableBalance` to `getAvailableBalanceAttribute` (method) or `available_balance` (property).

---

Cart methods now support fluent-dto. It is necessary to replace the old code with a new one, for example:

```php
// old
$cart = app(\Bavix\Wallet\Objects\Cart::class)
    ->addItems($products)
    ->addItem($product)
    ->setMeta(['hello' => 'world']);
    
$cart->addItem($product);

// new. fluent
$cart = app(\Bavix\Wallet\Objects\Cart::class)
    ->withItems($products)
    ->withItem($product)
    ->withMeta(['hello' => 'world']);

$cart = $cart->withItem($product);
```

## 8.1.x+ → 9.0.x

> The logic of storing transfers between accounts has changed.
> Previously, money could be credited to the user directly, but starting from version nine, all transactions go strictly between wallets. 
> Thanks to this approach, finally, there will be full-fledged work with uuid identifiers in the project.

To migrate to the correct structure, you need to run the command:
```
artisan bx:transfer:fix
```

If the command fails, then the command must be restarted. 
Continue until the command starts executing immediately (no bad entries left).

---

The product has been divided into two interfaces:
- `ProductLimitedInterface`. Needed to create limited goods;
- `ProductInterface`. Needed for an infinite number of products;

The old Product interface should be replaced with one of these.

Replace `Bavix\Wallet\Interfaces\Product` to `Bavix\Wallet\Interfaces\ProductLimitedInterface`. 
