# Helper FormatterServiceInterface

Sometimes you need to convert the balance to some format. A small and simple helper has appeared that will simplify the process a little.

### floatValue

```php
app(FormatterServiceInterface::class)->floatValue('12345', 2); // 123.45
app(FormatterServiceInterface::class)->floatValue('12345', 3); // 12.345
```

### intValue

```php
app(FormatterServiceInterface::class)->intValue('12.345', 3); // 12345
app(FormatterServiceInterface::class)->intValue('123.45', 2); // 12345
```

---
It's simple!
