<?php

namespace Bavix\Wallet;

use Bavix\Wallet\Commands\RefreshBalance;
use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Objects\EmptyLock;
use Bavix\Wallet\Objects\Operation;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\LockService;
use Bavix\Wallet\Services\ProxyService;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Simple\Rate;
use Bavix\Wallet\Simple\Store;
use Illuminate\Support\ServiceProvider;
use function config;
use function dirname;
use function function_exists;

class WalletServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     * @codeCoverageIgnore
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(
            dirname(__DIR__) . '/resources/lang',
            'wallet'
        );

        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([RefreshBalance::class]);

        $this->loadMigrationsFrom([
            __DIR__ . '/../database/migrations_v1',
            __DIR__ . '/../database/migrations_v2',
        ]);

        if (function_exists('config_path')) {
            $this->publishes([
                dirname(__DIR__) . '/config/config.php' => config_path('wallet.php'),
            ], 'laravel-wallet-config');
        }

        $this->publishes([
            dirname(__DIR__) . '/database/migrations_v1/' => database_path('migrations'),
            dirname(__DIR__) . '/database/migrations_v2/' => database_path('migrations'),
        ], 'laravel-wallet-migrations');

        $this->publishes([
            dirname(__DIR__) . '/database/migrations_v1/' => database_path('migrations'),
        ], 'laravel-wallet-migrations-v1');

        $this->publishes([
            dirname(__DIR__) . '/database/migrations_v2/' => database_path('migrations'),
        ], 'laravel-wallet-migrations-v2');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/config/config.php',
            'wallet'
        );

        // Bind eloquent models to IoC container
        $this->app->singleton(Rateable::class, config('wallet.package.rateable', Rate::class));
        $this->app->singleton(Storable::class, config('wallet.package.storable', Store::class));
        $this->app->singleton(DbService::class, config('wallet.services.db', DbService::class));
        $this->app->singleton(ExchangeService::class, config('wallet.services.exchange', ExchangeService::class));
        $this->app->singleton(CommonService::class, config('wallet.services.common', CommonService::class));
        $this->app->singleton(ProxyService::class, config('wallet.services.proxy', ProxyService::class));
        $this->app->singleton(WalletService::class, config('wallet.services.wallet', WalletService::class));
        $this->app->singleton(LockService::class, config('wallet.services.lock', LockService::class));

        // models
        $this->app->bind(Transaction::class, config('wallet.transaction.model', Transaction::class));
        $this->app->bind(Transfer::class, config('wallet.transfer.model', Transfer::class));
        $this->app->bind(Wallet::class, config('wallet.wallet.model', Wallet::class));

        // object's
        $this->app->bind(Bring::class, config('wallet.objects.bring', Bring::class));
        $this->app->bind(Cart::class, config('wallet.objects.cart', Cart::class));
        $this->app->bind(EmptyLock::class, config('wallet.objects.emptyLock', EmptyLock::class));
        $this->app->bind(Operation::class, config('wallet.objects.operation', Operation::class));
    }

}
