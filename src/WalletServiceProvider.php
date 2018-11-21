<?php

namespace Bavix\Wallet;

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

        $this->publishes([
            \dirname(__DIR__) . '/config/config.php' => config_path('wallet.php'),
        ], 'laravel-wallet-config');

        $this->publishes([
            \dirname(__DIR__) . '/database/migrations_v1/' => database_path('migrations'),
            \dirname(__DIR__) . '/database/migrations_v2/' => database_path('migrations'),
        ], 'laravel-wallet-migrations');

        $this->publishes([
            \dirname(__DIR__) . '/database/migrations_v1/' => database_path('migrations'),
        ], 'laravel-wallet-migrations-v1');

        $this->publishes([
            \dirname(__DIR__) . '/database/migrations_v2/' => database_path('migrations'),
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
            \dirname(__DIR__) . '/config/config.php',
            'wallet'
        );

        // Bind eloquent models to IoC container
        $this->app->singleton('bavix.wallet::transaction', config('wallet.transaction.model'));
        $this->app->singleton('bavix.wallet::transfer', config('wallet.transfer.model'));
        $this->app->singleton('bavix.wallet::wallet', config('wallet.wallet.model'));
    }

}
