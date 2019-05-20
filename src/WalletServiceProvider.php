<?php

namespace Bavix\Wallet;

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\ProxyService;
use Bavix\Wallet\Services\WalletService;
use Illuminate\Support\ServiceProvider;

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
            \dirname(__DIR__) . '/resources/lang',
            'wallet'
        );

        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->loadMigrationsFrom([
            __DIR__.'/../database/migrations_v1',
            __DIR__.'/../database/migrations_v2',
            __DIR__.'/../database/migrations_v3',
        ]);

        if (\function_exists('config_path')) {
            $this->publishes([
                \dirname(__DIR__) . '/config/config.php' => config_path('wallet.php'),
            ], 'laravel-wallet-config');
        }

        $this->publishes([
            \dirname(__DIR__) . '/database/migrations_v1/' => database_path('migrations'),
            \dirname(__DIR__) . '/database/migrations_v2/' => database_path('migrations'),
            \dirname(__DIR__) . '/database/migrations_v3/' => database_path('migrations'),
        ], 'laravel-wallet-migrations');

        $this->publishes([
            \dirname(__DIR__) . '/database/migrations_v1/' => database_path('migrations'),
        ], 'laravel-wallet-migrations-v1');

        $this->publishes([
            \dirname(__DIR__) . '/database/migrations_v2/' => database_path('migrations'),
        ], 'laravel-wallet-migrations-v2');

        $this->publishes([
            \dirname(__DIR__) . '/database/migrations_v3/' => database_path('migrations'),
        ], 'laravel-wallet-migrations-v3');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            \dirname(__DIR__) . '/config/config.php',
            'wallet'
        );

        // Bind eloquent models to IoC container
        $this->app->singleton(Transaction::class, \config('wallet.transaction.model'));
        $this->app->singleton(Transfer::class, \config('wallet.transfer.model'));
        $this->app->singleton(Wallet::class, \config('wallet.wallet.model'));
        $this->app->singleton(CommonService::class, \config('wallet.services.common'));
        $this->app->singleton(ProxyService::class, \config('wallet.services.proxy'));
        $this->app->singleton(WalletService::class, \config('wallet.services.wallet'));
    }

}
