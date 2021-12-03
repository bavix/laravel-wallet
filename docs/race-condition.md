## Race Condition

A common issue in the issue is about race conditions.

If you have not yet imported the config into the project, then you need to do this.
```bash
php artisan vendor:publish --tag=laravel-wallet-config
```

Previously, there was a vacuum package, but now it is a part of the core. You just need to configure the lock service and the cache service in the package configuration `wallet.php`.

```php
    /**
     * A system for dealing with race conditions.
     */
    'lock' => [
        'driver' => 'array',
        'seconds' => 1,
    ],
```

To enable the fight against race conditions, you need to select a provider that supports work with locks. I recommend `redis`.

There is a setting for storing the state of the wallet, I recommend choosing `redis` here too.

```php
    /**
     * Storage of the state of the balance of wallets.
     */
    'cache' => ['driver' => 'array'],
```

You need `redis-server` and `php-redis`.

Redis is recommended but not required. You can choose whatever the [framework](https://laravel.com/docs/8.x/cache#introduction) offers you.

It worked! 
