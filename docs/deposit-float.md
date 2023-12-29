# Deposit float

A deposit is a sum of money which is part of the full price of something, 
and which you pay when you agree to buy it.

In this case, the Deposit is the replenishment of the wallet.

---

## User Model

[User Simple](_include/models/user_simple_float.md ':include')

## Make a Deposit

Find user:

```php
$user = User::first(); 
```

As the user uses `HasWalletFloat`, he will have `balance` property. 
Check the user's balance.

```php
$user->balance; // 0
$user->balanceInt; // 0
$user->balanceFloatNum; // 0
```

The balance is zero, which is what we expected.

```php
$user->depositFloat(10.1); 
$user->balance; // 1010
$user->balanceInt; // 1010
$user->balanceFloatNum; // 10.1
```

Wow!
