# Transfer

Transfer in our system are two well-known [Deposit](deposit-float) and [Withdraw](withdraw-float) 
operations that are performed in one transaction.

The transfer takes place between wallets.

---

## User Model

[User Simple](_include/models/user_simple_float.md ':include')

## Make a Transfer

Find user:

```php
$first = User::first(); 
$last = User::orderBy('id', 'desc')->first(); // last user
$first->getKey() !== $last->getKey(); // true
```

As the user uses `HasWalletFloat`, he will have `balance` property. 
Check the user's balance.

```php
$fist->balanceFloatNum; // 100.00
$last->balanceFloatNum; // 0
```

The transfer will be from the first user to the second.

```php
$first->transferFloat($last, 5); 
$first->balanceFloatNum; // 95
$last->balanceFloatNum; // 5
```

It worked! 

## Force Transfer

Check the user's balance.

```php
$first->balanceFloatNum; // 100
$last->balanceFloatNum; // 0
```

The transfer will be from the first user to the second.

```php
$first->forceTransferFloat($last, 500); 
$first->balanceFloatNum; // -400
$last->balanceFloatNum; // 500
```

It worked! 
