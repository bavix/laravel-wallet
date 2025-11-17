# Merchant Fee Deductible

The `MerchantFeeDeductible` interface allows you to deduct fees from the merchant's payout instead of adding them to the customer's payment. This means customers pay only the listed product price, while merchants receive the product price minus the fee.

## How It Works

By default, when using the `Taxable` interface, fees are added to the customer's payment:

- **Product price:** $100
- **Fee:** 5%
- **Customer pays:** $105 ($100 + $5 fee)
- **Merchant receives:** $100

When using `MerchantFeeDeductible`, fees are deducted from the merchant's payout:

- **Product price:** $100
- **Fee:** 5%
- **Customer pays:** $100
- **Merchant receives:** $95 ($100 - $5 fee)

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

Add the `HasWallet` trait and implement both `ProductInterface` (or `ProductLimitedInterface`) and `MerchantFeeDeductible` interfaces to your Item model.

The `MerchantFeeDeductible` interface extends `Taxable`, so you need to implement the `getFeePercent()` method.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\MerchantFeeDeductible;
use Bavix\Wallet\Interfaces\ProductLimitedInterface;

class Item extends Model implements ProductLimitedInterface, MerchantFeeDeductible
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

    /**
     * Specify the percentage of the amount. For example, the product costs $100, the fee is 5%.
     * With MerchantFeeDeductible, customer pays $100, merchant receives $95.
     *
     * Minimum 0; Maximum 100
     */
    public function getFeePercent(): float|int
    {
        return 5.0; // 5%
    }
}
```

## Payment Process

Find the user and check the balance.

```php
$user = User::first();
$user->balance; // 100
```

Find the goods and check the cost.

```php
$item = Item::first();
$item->getAmountProduct($user); // 100
```

The user can buy a product. With `MerchantFeeDeductible`, the customer only needs to pay the product price (no fee added).

```php
$user->pay($item); // success, customer pays $100 (product price)
$user->balance; // 0
```

After payment:
- Customer balance: $0 (paid $100)
- Merchant balance: $95 (received $100 - $5 fee)
- Transfer fee: $5

## Combining with MinimalTaxable and MaximalTaxable

You can combine `MerchantFeeDeductible` with `MinimalTaxable` or `MaximalTaxable` interfaces to set minimum or maximum fee limits.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\MerchantFeeDeductible;
use Bavix\Wallet\Interfaces\MinimalTaxable;
use Bavix\Wallet\Interfaces\ProductInterface;

class Item extends Model implements ProductInterface, MerchantFeeDeductible, MinimalTaxable
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

    public function getFeePercent(): float|int
    {
        return 0.03; // 3%    
    }
    
    public function getMinimalFee(): int|string
    {
        return 5; // 3%, minimum 5    
    }
}
```

#### Example with Minimal Fee

Find the user and check the balance.

```php
$user = User::first();
$user->balance; // 100
```

Find the goods and check the cost.

```php
$item = Item::first();
$item->getAmountProduct($user); // 100
```

The user can buy a product. With `MerchantFeeDeductible` and `MinimalTaxable`:
- Customer pays: $100 (product price, no fee added)
- Merchant receives: $95 ($100 - $5 minimal fee)

```php
$user->pay($item); // success, customer pays $100
$user->balance; // 0
```

## Gifts

The `MerchantFeeDeductible` interface also works with gift payments. When gifting a product, the customer pays only the product price, and the merchant receives the product price minus the fee.

```php
$santa = User::first();
$child = User::find(2);

$item = Item::first();
$item->getAmountProduct($santa); // 100

// Santa deposits only the product price
$santa->deposit(100);
$santa->balance; // 100

// Gift the product
$transfer = $santa->wallet->gift($child, $item);

// Santa's balance: 0 (paid $100)
// Child received the product
// Merchant balance: $95 (received $100 - $5 fee)
```

## Refunds

When refunding a purchase made with `MerchantFeeDeductible`, the customer receives back what the merchant received (product price minus fee), not what the customer originally paid.

```php
$user = User::first();
$item = Item::first();

// Customer pays $100, merchant receives $95
$user->pay($item);
$user->balance; // 0
$item->balance; // 95

// Refund
$user->refund($item);
$user->balance; // 95 (what merchant received)
$item->balance; // 0
```

## Direct Transfers

When transferring directly to a product that implements `MerchantFeeDeductible`, the fee is deducted from the recipient's deposit.

```php
$user = User::first();
$item = Item::first(); // implements MerchantFeeDeductible

$user->deposit(100);
$user->balance; // 100

// Transfer to merchant
$transfer = $user->transfer($item, 100);

// User balance: 0
// Merchant balance: 95 ($100 - $5 fee)
```

## Key Differences from Taxable

| Feature | Taxable | MerchantFeeDeductible |
|---------|---------|----------------------|
| Customer pays | Product price + fee | Product price only |
| Merchant receives | Product price | Product price - fee |
| Fee location | Added to customer payment | Deducted from merchant payout |
| Use case | Customer covers transaction costs | Merchant covers transaction costs |

It's simple!

