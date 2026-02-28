# Race Condition

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

## PostgreSQL Row-Level Locks

When using PostgreSQL with `lock.driver = 'database'`, the package automatically uses PostgreSQL-specific row-level locks (`SELECT ... FOR UPDATE`) for optimal performance and data consistency.

### Benefits

- **Database-level locking**: Locks are managed directly by PostgreSQL, ensuring true atomicity
- **Better performance**: Single query locks multiple wallets at once, reducing database round trips
- **Automatic cache management**: The package automatically forces `array` cache driver when using PostgreSQL locks, as database-level locks ensure consistency without external cache synchronization

### How It Works

When you configure:
```php
'lock' => [
    'driver' => 'database',
],
```

And your database connection is PostgreSQL, the package automatically:
1. Uses `PostgresLockService` instead of standard `LockService`
2. Locks wallets using `SELECT ... FOR UPDATE` at the database level
3. Forces `array` cache driver for optimal performance (external cache becomes redundant)

### Important Notes

- **Automatic selection**: No additional configuration needed - works automatically when `lock.driver = 'database'` and database is PostgreSQL
- **Array cache**: When using PostgreSQL locks, the package automatically forces `array` cache driver. This is **CRITICAL** because:
  - Before locking, balance **MUST** be read from DB with `FOR UPDATE`
  - This balance is synced to StorageService (state transaction) via `multiSync()`
  - External cache (database, redis, memcached) would be redundant and could cause inconsistencies
  - Array cache ensures balance is always fresh from DB within transaction
- **Other databases**: For non-PostgreSQL databases, standard Laravel database locks are used
- **Backward compatible**: All existing code continues to work without changes

It's simple!
