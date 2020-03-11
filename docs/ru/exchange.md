### Обмен

Задачи у всех разные и с помощью данной функциональности 
можно добавить курсы валют в ваши кошельки.

Конфигурация валют происходит в общем файле конфигурации `config/wallet.php`. 

```php
    'currencies' => [
        'xbtc' => 'BTC',
        'dollar' => 'USD',
        'ruble' => 'RUB',
    ],
```

Ключ в конфигурации это `slug` вашего кошелька.
Значение, это валюта вашего кошелька.

Сервис для работы с валютами вам нужно написать самому или 
использовать [готовую библиотеку](https://github.com/bavix/laravel-wallet-swap).

#### Сервис для работы с валютой

Напишем простой сервис. Данные будем брать из массива, а не из базы данных.

```php
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Services\WalletService;
use Illuminate\Support\Arr;

class MyRateService extends \Bavix\Wallet\Simple\Rate
{

    // список курса валют (берёте из базы данных)
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

#### Регистрация сервиса 

Написанный вами сервис необходимо зарегистрировать, делается это в файле `config/wallet.php`.

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

#### Процесс обмена

Создадим два кошелька.

```php
$usd = $user->createWallet([
    'name' => 'My Dollars',
    'slug' => 'dollar',
]);

$rub = $user->createWallet([
    'name' => 'Мои рубли',
    'slug' => 'ruble',
]);
```

Пополним рублевый кошелёк на 100 рублей.

```php
$rub->deposit(10000);
```

Переведём рубли в доллары.

```php
$transfer = $rub->exchange($usd, 10000);
$rub->balance; // int(0)
$usd->balance; // int(147), это $1.47
```

К сожалению, мир не идеален. Вы не получите обратно свои 100 рублей.  

```php
$transfer = $usd->exchange($rub, $usd->balance);
$usd->balance; int(0)
$rub->balance; int(9938)
```

За счёт конвертации и математических округлений вы потеряли 62 копейки.
У вас осталось 99 рублей 38 копеек.

---
Это работает!
