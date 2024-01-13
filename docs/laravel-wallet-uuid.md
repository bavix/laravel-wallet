## Laravel Wallet UUID

> Using uuid greatly reduces package performance. We recommend using int.

Often there is a need to store an identifier in uuid/ulid. Since version 9.0 laravel-wallet supports string identifiers, you only need to perform the migration.

To simplify the process, you can use a ready-made package.

> Attention! It will not work to use UUID instead of ID in wallet models; there is a special uuid field for this.

## Composer

The recommended installation method is using [Composer](https://getcomposer.org/).

In your project root just run:

```bash
composer req bavix/laravel-wallet-uuid
```

Now you need to migrate!

After migration, you can use the UUID in your models.

You can find implementation examples in the package tests: https://github.com/bavix/laravel-wallet-uuid/tree/master/tests

It worked! 
