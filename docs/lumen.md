[composer](_include/composer.md ':include')

> Note: In the years since releasing Lumen, PHP has made a variety of wonderful performance improvements. For this reason, along with the availability of Laravel Octane, we no longer recommend that you begin new projects with Lumen. Instead, we recommend always beginning new projects with Laravel.

## Add the service to the app

[Editing the application file](https://lumen.laravel.com/docs/5.8/providers#registering-providers) `bootstrap/app.php`
```php
$app->register(\Bavix\Wallet\WalletServiceProvider::class);
```

You also need to add two lines to the "Register Container Bindings" section of the bootstrap/app.php file:
```php
\Illuminate\Support\Facades\Cache::setApplication($app);
$app->registerDeferredProvider(\Illuminate\Cache\CacheServiceProvider::class);
```

Make sure you have Facades and Eloquent enabled.
```php
$app->withFacades();

$app->withEloquent();
```

Start the migration and use the library.

## You can use it for customization

Sometimes it is useful...

### Run Migrations
Publish the migrations with this artisan command:
```bash
php artisan vendor:publish --tag=laravel-wallet-migrations
```

### Configuration
You can publish the config file with this artisan command:
```bash
php artisan vendor:publish --tag=laravel-wallet-config
```

After installing the package, you can proceed to [use it](basic-usage).
