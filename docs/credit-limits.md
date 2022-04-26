## Credit Limits

If you need the ability to have wallets have a credit limit, then this functionality is for you.

The functionality does nothing, it only allows you not to use "force" for most of the operations within the credit limit. You yourself write the logic for collecting interest, notifications on debts, etc.

By default, the credit limit is zero.

An example of working with a credit limit:
```php
/**
 * @var \Bavix\Wallet\Interfaces\Customer $customer
 * @var \Bavix\Wallet\Models\Wallet $wallet
 * @var \Bavix\Wallet\Interfaces\ProductInterface $product
 */
$wallet = $customer->wallet; // get default wallet
$wallet->meta['credit'] = 10000; // credit limit
$wallet->save(); // update credit limit

$wallet->balanceInt; // 0
$product->getAmountProduct($customer); // 500

$wallet->pay($product); // success
$wallet->balanceInt; // -500
```

For multi-wallets when creating:
```php
/** @var \Bavix\Wallet\Traits\HasWallets $user */
$wallet = $user->createWallet([
    'name' => 'My Wallet',
    'meta' => ['credit' => 500],
]);
```

It worked! 
