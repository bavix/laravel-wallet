<?php

declare(strict_types=1);

namespace Bavix\Wallet;

use Bavix\Wallet\Commands\RefreshBalance;
use Bavix\Wallet\Contracts\CastInterface;
use Bavix\Wallet\Contracts\LockInterface;
use Bavix\Wallet\Contracts\MathInterface;
use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Objects\EmptyLock;
use Bavix\Wallet\Objects\Operation;
use Bavix\Wallet\Services\AtomicLockService;
use Bavix\Wallet\Services\CastService;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\LockService;
use Bavix\Wallet\Services\MathService;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Simple\Rate;
use Bavix\Wallet\Simple\Store;
use function config;
use function dirname;
use function function_exists;
use Illuminate\Support\ServiceProvider;

class WalletServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @codeCoverageIgnore
     */
    public function boot(): void
    {
        $this->loadTranslationsFrom(
            dirname(__DIR__).'/resources/lang',
            'wallet'
        );

        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([RefreshBalance::class]);

        if ($this->shouldMigrate()) {
            $this->loadMigrationsFrom([__DIR__.'/../database']);
        }

        if (function_exists('config_path')) {
            $this->publishes([
                dirname(__DIR__).'/config/config.php' => config_path('wallet.php'),
            ], 'laravel-wallet-config');
        }

        if (function_exists('database_path')) {
            $this->publishes([
                dirname(__DIR__).'/database/' => database_path('migrations'),
            ], 'laravel-wallet-migrations');
        }
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/config/config.php',
            'wallet'
        );

        $this->app->singleton(LockInterface::class, AtomicLockService::class);
        $this->app->singleton(CastInterface::class, CastService::class);

        // drop it
        $this->app->singleton(LockService::class, config('wallet.services.lock', LockService::class));
        $this->app->bind(EmptyLock::class, config('wallet.objects.emptyLock', EmptyLock::class));

        // internal
        $this->app->singleton(DbService::class, config('wallet.services.db', DbService::class));

        $this->app->singleton(MathInterface::class, config('wallet.package.mathable', MathService::class));
        $this->app->alias(MathInterface::class, Mathable::class); // tmp..

        // external
        $this->app->singleton(ExchangeService::class, config('wallet.services.exchange', ExchangeService::class));
        $this->app->singleton(WalletService::class, config('wallet.services.wallet', WalletService::class));

        // needle?
        $this->app->singleton(CommonService::class, config('wallet.services.common', CommonService::class));

        // Bind eloquent models to IoC container
        $this->app->singleton(Rateable::class, config('wallet.package.rateable', Rate::class));
        $this->app->singleton(Storable::class, config('wallet.package.storable', Store::class));

        // models
        $this->app->bind(Transaction::class, config('wallet.transaction.model', Transaction::class));
        $this->app->bind(Transfer::class, config('wallet.transfer.model', Transfer::class));
        $this->app->bind(Wallet::class, config('wallet.wallet.model', Wallet::class));

        // object's
        $this->app->bind(Bring::class, config('wallet.objects.bring', Bring::class));
        $this->app->bind(Cart::class, config('wallet.objects.cart', Cart::class));
        $this->app->bind(Operation::class, config('wallet.objects.operation', Operation::class));
    }

    /**
     * Determine if we should register the migrations.
     */
    protected function shouldMigrate(): bool
    {
        return WalletConfigure::$runsMigrations;
    }
}
