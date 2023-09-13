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
## Configure default wallet
You can customize the configuration file to suit certain needs. Example: 

Customize `name` and `slug` of default wallet.
```php[config/wallet.php]
'default' => [
            'name' => 'Ethereum',
            'slug' => 'ETH',
            'meta' => [],
        ],
```
## Extend Wallet class
You can extend the Wallet class by creating a new class that extends `Bavix\Wallet\Models\Wallet` and registering the new class in `config/wallet.php`.
Example `MyWallet.php`

```php[App/Models/MyWallet.php]
use Bavix\Wallet\Models\Wallet as WalletBase;

class MyWallet extends WalletBase {
    public function helloWorld(): string { return "hello world"; }
}
```

```php[config/wallet.php]
    'wallet' => [
        'table' => 'wallets',
        'model' => MyWallet::class,
        'creating' => [],
        'default' => [
            'name' => 'Default Wallet',
            'slug' => 'default',
            'meta' => [],
        ],
    ],
```
```php
   echo $user->wallet->helloWorld();
```

