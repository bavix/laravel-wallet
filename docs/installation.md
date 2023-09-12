[composer](_include/composer.md ':include')

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

You can customize the configuration file to suit certain needs. Example: 

Customize `name` and `slug` of default wallet.
```php[config/wallet.php]
'default' => [
            'name' => 'Ethereum',
            'slug' => 'ETH',
            'meta' => [],
        ],
```
