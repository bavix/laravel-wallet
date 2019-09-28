## User Model

Добавьте `CanConfirm` trait и `Confirmable` interface в модель User.

```php
use Bavix\Wallet\Interfaces\Confirmable;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Traits\CanConfirm;
use Bavix\Wallet\Traits\HasWallet;

class UserConfirm extends Model implements Wallet, Confirmable
{
    use HasWallet, CanConfirm;
}
```

### Example:

Иногда, необходимо подтвердить операцию и пересчитать баланс.
Теперь это доступно в библиотеке из коробки. Вот пример:

```php
$user->balance; // int(0)
$transaction = $user->deposit(100, null, false); // не подтверждена
$transaction->confirmed; // bool(false)
$user->balance; // int(0)

$user->confirm($transaction); // bool(true)
$transaction->confirmed; // bool(true)

$user->balance; // int(100) 
```

Это работает!
