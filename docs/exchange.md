### Exchange

Everyone’s tasks are different and with the help of this functionality
you can add exchange rates to your wallets.

The wallet currency is set via meta. Example:

```php
$user->createWallet([
    'name' => 'My USD Wallet',
    'meta' => ['currency' => 'USD'],
]);
```

Service for working with currencies you need to write yourself or
use [library](https://github.com/bavix/laravel-wallet-swap).

#### Service for working with currency

We will write a simple service. 
We will take the data from the array, and not from the database.

```php
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Services\ExchangeServiceInterface;

class MyExchangeService implements ExchangeServiceInterface
{
    private array $rates = [
        'USD' => [
            'RUB' => 67.61,
        ],
    ];

    private MathServiceInterface $mathService;

    public function __construct(MathServiceInterface $mathService)
    {
        $this->mathService = $mathService;

        foreach ($this->rates as $from => $rates) {
            foreach ($rates as $to => $rate) {
                if (empty($this->rates[$to][$from])) {
                    $this->rates[$to][$from] = $this->mathService->div(1, $rate);
                }
            }
        }
    }

    /** @param float|int|string $amount */
    public function convertTo(string $fromCurrency, string $toCurrency, $amount): string
    {
        return $this->mathService->mul($amount, $this->rates[$fromCurrency][$toCurrency] ?? 1);
    }
}
```

#### Service Registration

The service you wrote must be registered, this is done in the file `config/wallet.php`.

```php
return [
    // ...
    'services' => [
        'exchange' => MyExchangeService::class,
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
    'meta' => ['currency' => 'USD'],
]);

$rub = $user->createWallet([
    'name' => 'My Ruble',
    'meta' => ['currency' => 'RUB'],
]);
```

We replenish the ruble wallet with 100 rubles.

```php
$rub->deposit(10000);
```

We will exchange rubles into dollars.

```php
$transfer = $rub->exchange($usd, 10000);
$rub->balance; // 0
$usd->balance; // 147, это $1.47
```

Unfortunately, the world is not perfect. You will not get back your 100 rubles.

```php
$transfer = $usd->exchange($rub, $usd->balance);
$usd->balance; 0
$rub->balance; 9938
```

Due to conversion and mathematical rounding, you lost 62 kopecks.
You have 99 rubles 38 kopecks left.

---
It worked! 

