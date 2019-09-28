# Обновление

## 1.x.x → 2.x.x

Замените `::with('balance')` на `::with('wallet')`

---

## 2.1.x → 2.2.x

Замените `CanBePaid` на `CanPay`.

Замените `CanBePaidFloat` на `CanPayFloat`.

---

## 2.2.x → 2.4.x

Замените `calculateBalance` на `refreshBalance`

---

## 2.4.x → 3.0.x

Замените путь `bavix.wallet::transaction` на `Bavix\Wallet\Models\Transaction::class`

Замените путь `bavix.wallet::transfer` на `Bavix\Wallet\Models\Transfer::class`

Замените путь `bavix.wallet::wallet` на `Bavix\Wallet\Models\Wallet::class`

```php
// старый вариант
app('bavix.wallet::transaction'); 
// новый вариант
app(Bavix\Wallet\Models\Transaction::class); 
```

Необходимо добавить `$quantity` параметр в метод `canBuy`.

```php
// старый вариант
public function canBuy(Customer $customer, bool $force = false): bool
// новый вариант
public function canBuy(Customer $customer, int $quantity = 1, bool $force = null): bool
```

Необходимо добавить метод `getUniqueId` в Interface `Product`.

```php
class Item extends Model implements Product
{
    
    // Ваш код...
    
    public function getUniqueId(): string
    {
        return (string)$this->getKey();
    }
    
}
```

## 3.0.x → 3.1.x

Замените `Taxing` на `Taxable`.
