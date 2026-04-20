# Laravel Wallet UUID <VersionTag version="v9.0.0" />

> Using uuid greatly reduces package performance. We recommend using int.

If you need UUID/ULID identifiers, Laravel Wallet supports string identifiers since v9.0.0, so you only need to run the migration.

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

It's simple!
