# Пересчёт баланса

Когда вы создаете множество неподтвержденных операций,
то когда из подтвердит, к примеру, модератор
вы можете увидеть, что средств на счёте пользователя нет.

Для этого существует принудительный пересчёт баланса.

---

## Пользователь

Подтоговим модель, добавив `HasWallet` trait и `Wallet` interface.

```php
use Bavix\Wallet\Traits\HasWallet;
use Bavix\Wallet\Interfaces\Wallet;

class User extends Model implements Wallet
{
    use HasWallet;
}
```

## Пересчёт

Проверим баланс.

```php
$user->id; // int(5)
$user->balance; // int(27)
```

Подтвердим операции пользователя.

```sql
update transactions 
set confirmed=1 
where confirmed=0 and 
      payable_type='App\Models\User' and 
      payable_id=5;
-- 212 rows affected in 54 ms
```

Операций было 212, пересчитаем баланс.

```php
$user->balance; // int(27)
$user->wallet->refreshBalance();
$user->balance; // int(42)
```

Просто работает!
