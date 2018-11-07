<?php

namespace Bavix\Wallet;

use Illuminate\Support\ServiceProvider;

class WalletServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
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
            \dirname(__DIR__) . '/database/migrations/' => database_path('migrations'),
        ], 'laravel-wallet-migrations');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(\dirname(__DIR__) . '/config/config.php', 'wallet');
    }

}
