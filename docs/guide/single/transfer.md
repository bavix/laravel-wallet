# Transfer

Transfer in our system are two well-known [Deposit](deposit.md) and [Withdraw](withdraw.md) 
operations that are performed in one transaction.

The transfer takes place between wallets.

## User Model

<!--@include: ../../_include/models/user_simple.md -->

### Example contract

```php
$transfer = $user1->transfer(
    $user2,
    511,
    new Extra(
        deposit: [
            'type' => 'extra-deposit',
        ],
        withdraw: new Option(
            [
                'type' => 'extra-withdraw',
            ],
            false // confirmed
        ),
        extra: [
            'msg' => 'hello world',
        ],
    )
);
```

## Make a Transfer

Find user:

```php
$first = User::first(); 
$last = User::orderBy('id', 'desc')->first(); // last user
$first->getKey() !== $last->getKey(); // true
```

As the user uses `HasWallet`, he will have `balance` property. 
Check the user's balance.

```php
$first->balance; // 100
$last->balance; // 0
```

The transfer will be from the first user to the second.

```php
$first->transfer($last, 5); 
$first->balance; // 95
$last->balance; // 5
```

It's simple!

## Force Transfer

Check the user's balance.

```php
$first->balance; // 100
$last->balance; // 0
```

The transfer will be from the first user to the second.

```php
$first->forceTransfer($last, 500); 
$first->balance; // -400
$last->balance; // 500
```

It's simple!
