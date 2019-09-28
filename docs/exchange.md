### Exchange

Everyone’s tasks are different and with the help of this functionality
you can add exchange rates to your wallets.

Currencies are configured in the general configuration file `config/wallet.php`.

```php
    'currencies' => [
        'xbtc' => 'BTC',
        'dollar' => 'USD',
        'ruble' => 'RUB',
    ],
```

The key in the configuration is the `slug` of your wallet.
Value, this is the currency of your wallet.

Service for working with currencies you need to write yourself or
use [library](https://github.com/bavix/laravel-wallet-swap).

#### Service for working with currency

We will write a simple service. 
We will take the data from the array, and not from the database.

```php
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Services\WalletService;
use Illuminate\Support\Arr;

class MyRateService extends \Bavix\Wallet\Simple\Rate
{

    // list of exchange rates (take from the database)
    protected $rates = [
        'USD' => [
            'RUB' => 67.61,
        ],
        'RUB' => [
            'USD' => 0.0147907114,
        ],
    ];

    protected function rate(Wallet $wallet): float
    {
        $from = app(WalletService::class)->getWallet($this->withCurrency);
        $to = app(WalletService::class)->getWallet($wallet);

        return Arr::get(
            Arr::get($this->rates, $from->currency, []),
            $to->currency,
            1
        );
    }

    public function convertTo(Wallet $wallet): float
    {
        return parent::convertTo($wallet) * $this->rate($wallet);
    }

}

```

#### Service Registration

The service you wrote must be registered, this is done in the file `config/wallet.php`.

```php
return [
    // ...
    'package' => [
        'rateable' => MyRateService::class,
        // ...
    ],
    // ...
];
```

#### Exchange process

Create two wallets.

```php
$usd = $user->createWallet([
    'name' => 'My Dollars',
    'slug' => 'dollar',
]);

$rub = $user->createWallet([
    'name' => 'My Rub',
    'slug' => 'ruble',
]);
```

We replenish the ruble wallet with 100 rubles.

```php
$rub->deposit(10000);
```

We will exchange rubles into dollars.

```php
$transfer = $rub->exchange($usd, 10000);
$rub->balance; // int(0)
$usd->balance; // int(147), это $1.47
```

Unfortunately, the world is not perfect. You will not get back your 100 rubles.

```php
$transfer = $usd->exchange($rub, $usd->balance);
$usd->balance; int(0)
$rub->balance; int(9938)
```

Due to conversion and mathematical rounding, you lost 62 kopecks.
You have 99 rubles 38 kopecks left.

---
It worked! 

