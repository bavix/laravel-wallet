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
    }

}
