# Commissions

Commissions are part of purchasing goods. The commission is debited from the buyer's account and is not credited for the product. This amount is lost along the way. The commission amount can always be found in the fee column.

## User Model

Add the `CanPay` trait and `Customer` interface to your User model.

> The trait `CanPay` already inherits `HasWallet`, reuse will cause an error.

```php
use Bavix\Wallet\Traits\CanPay;
use Bavix\Wallet\Interfaces\Customer;

class User extends Model implements Customer
{
    use CanPay;
}
```

## Item Model

Add the `HasWallet` trait and `ProductInterface` (or `ProductLimitedInterface`) interface to Item model.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Taxable;
use Bavix\Wallet\Interfaces\ProductLimitedInterface;

class Item extends Model implements ProductLimitedInterface, Taxable
{
    use HasWallet;

    public function canBuy(Customer $customer, int $quantity = 1, bool $force = false): bool
    {
        /**
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

    public function getFeePercent()
    {
        return 0.03; // 3%    
    }
}
```

## Tax process

Find the user and check the balance.

```php
$user = User::first();
$user->balance; // 103
```

Find the goods and check the cost.

```php
$item = Item::first();
$item->getAmountProduct($user); // 100
```

The user can buy a product, buy...

```php
$user->pay($item); // success, 100 (product) + 3 (fee) = 103
$user->balance; // 0
```

## Minimal Taxing

Add interface `MinimalTaxable` (or `MaximalTaxable`) in class `Item`.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\MinimalTaxable;
use Bavix\Wallet\Interfaces\ProductInterface;

class Item extends Model implements ProductInterface, MinimalTaxable
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

    public function getFeePercent()
    {
        return 0.03; // 3%    
    }
    
    public function getMinimalFee()
    {
        return 5; // 3%, minimum 5    
    }
}
```

#### Successfully

Find the user and check the balance.

```php
$user = User::first();
$user->balance; // 105
```

Find the goods and check the cost.

```php
$item = Item::first();
$item->getAmountProduct($user); // 100
```

The user can buy a product, buy...

```php
$user->pay($item); // success, 100 (product) + 5 (minimal fee) = 105
$user->balance; // 0
```

#### Failed

Find the user and check the balance.

```php
$user = User::first();
$user->balance; // 103
```

Find the goods and check the cost.

```php
$item = Item::first();
$item->getAmountProduct($user); // 100
```

The user can buy a product, buy...

```php
$user->safePay($item); // failed, 100 (product) + 5 (minimal fee) = 105
$user->balance; // 103
```

## Alternative: Merchant Fee Deductible

By default, the `Taxable` interface adds fees to the customer's payment. If you want to deduct fees from the merchant's payout instead (so customers pay only the product price), you can use the `MerchantFeeDeductible` interface.

**With Taxable (default behavior):**
- Product price: $100
- Fee: 5%
- Customer pays: $105 ($100 + $5 fee)
- Merchant receives: $100

**With MerchantFeeDeductible:**
- Product price: $100
- Fee: 5%
- Customer pays: $100
- Merchant receives: $95 ($100 - $5 fee)

For more information, see the [Merchant Fee Deductible](/guide/purchases/merchant-fee-deductible) documentation.

It's simple!
