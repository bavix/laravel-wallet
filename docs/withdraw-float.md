# Withdraw

When there is enough money in the account, you can transfer/withdraw 
it or buy something in the system.

Since the currency is virtual, you can buy any services on your website. 
For example, priority in search results.

---

## User Model

[User Simple](_include/models/user_simple_float.md ':include')

## Make a Withdraw

Find user:

```php
$user = User::first(); 
```

As the user uses `HasWalletFloat`, he will have `balance` property. 
Check the user's balance.

```php
$user->balance; // 10000
$user->balanceInt; // 10000
$user->balanceFloatNum; // 100.00
```

The balance is not empty, so you can withdraw funds.

```php
$user->withdrawFloat(10); 
$user->balance; // 9000
$user->balanceInt; // 9000
$user->balanceFloatNum; // 90.00
```

It worked! 

## Force Withdraw

Forced withdrawal is necessary for those cases when 
the user has no funds. For example, a fine for spam.

```php
$user->balanceFloatNum; // 90.00
$user->forceWithdrawFloat(101);
$user->balanceFloatNum; // -11.00
```

## And what will happen if the money is not enough?

There can be two situations:

- The user's balance is zero, then we get an error
`Bavix\Wallet\Exceptions\BalanceIsEmpty`
- If the balance is greater than zero, but it is not enough
`Bavix\Wallet\Exceptions\InsufficientFunds`
