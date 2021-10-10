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


