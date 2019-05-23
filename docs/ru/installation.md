# Установка

---

- [Composer](#composer)
- [Миграции](#run-migrations)
- [Конфигурация](#configuration)

<a name="composer"></a>
## Composer

Рекомендуем установку используя [Composer](https://getcomposer.org/).

В корне вашего проекта запустите:

```bash
composer req bavix/laravel-wallet
```

Убедитесь, что проект настроен на [autoload Composer-installed packages](https://getcomposer.org/doc/01-basic-usage.md#autoloading).

<a name="run-migrations"></a>
## Миграции
Опубликуйте миграции с помощью этой команды artisan:
```bash
php artisan vendor:publish --tag=laravel-wallet-migrations
```

<a name="configuration"></a>
## Configuration
Файл конфигурации можно опубликовать с помощью этой команды artisan:
```bash
php artisan vendor:publish --tag=laravel-wallet-config
```

После установки пакета можно перейти к [использованию](basic-usage).
