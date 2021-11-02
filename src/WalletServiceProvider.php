<?php

declare(strict_types=1);

namespace Bavix\Wallet;

use Bavix\Wallet\Commands\RefreshBalance;
use Bavix\Wallet\Interfaces\Mathable;
use Bavix\Wallet\Interfaces\Rateable;
use Bavix\Wallet\Interfaces\Storable;
use Bavix\Wallet\Internal\BasketInterface;
use Bavix\Wallet\Internal\BookkeeperInterface;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\ExchangeInterface;
use Bavix\Wallet\Internal\LockInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\PurchaseInterface;
use Bavix\Wallet\Internal\StorageInterface;
use Bavix\Wallet\Internal\UuidInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Objects\EmptyLock;
use Bavix\Wallet\Objects\Operation;
use Bavix\Wallet\Services\AtomicService;
use Bavix\Wallet\Services\BasketService;
use Bavix\Wallet\Services\BookkeeperService;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\ConsistencyService;
use Bavix\Wallet\Services\DbService;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\LockService;
use Bavix\Wallet\Services\MathService;
use Bavix\Wallet\Services\MetaService;
use Bavix\Wallet\Services\PurchaseService;
use Bavix\Wallet\Services\StorageService;
use Bavix\Wallet\Services\UuidFactoryService;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Simple\BrickMath;
use Bavix\Wallet\Simple\Exchange;
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

        $this->publishes([
            dirname(__DIR__).'/database/' => database_path('migrations'),
        ], 'laravel-wallet-migrations');
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

        $this->singletons();
        $this->legacySingleton();
        $this->bindObjects();
    }

    /**
     * Determine if we should register the migrations.
     */
    protected function shouldMigrate(): bool
    {
        return WalletConfigure::isRunsMigrations();
    }

    private function singletons(): void
    {
        // Bind eloquent models to IoC container
        $this->app->singleton(ExchangeInterface::class, config('wallet.package.exchange', Exchange::class));
        $this->app->singleton(MathInterface::class, config('wallet.package.mathable', MathService::class));
        $this->app->singleton(CommonService::class, config('wallet.services.common', CommonService::class));
        $this->app->singleton(WalletService::class, config('wallet.services.wallet', WalletService::class));

        $this->app->singleton(LockInterface::class, AtomicService::class);
        $this->app->singleton(UuidInterface::class, UuidFactoryService::class);
        $this->app->singleton(StorageInterface::class, StorageService::class);
        $this->app->singleton(BookkeeperInterface::class, BookkeeperService::class);
        $this->app->singleton(BasketInterface::class, BasketService::class);
        $this->app->singleton(ConsistencyInterface::class, ConsistencyService::class);
        $this->app->singleton(PurchaseInterface::class, PurchaseService::class);
    }

    private function legacySingleton(): void
    {
        $this->app->singleton(ExchangeService::class, config('wallet.services.exchange', ExchangeService::class));
        $this->app->singleton(Rateable::class, config('wallet.package.rateable', Rate::class));
        $this->app->singleton(Storable::class, config('wallet.package.storable', Store::class));

        $this->app->singleton(Mathable::class, BrickMath::class);

        $this->app->singleton(DbService::class, config('wallet.services.db', DbService::class));
        $this->app->singleton(LockService::class, config('wallet.services.lock', LockService::class));
        $this->app->singleton(MetaService::class);
    }

    private function bindObjects(): void
    {
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
