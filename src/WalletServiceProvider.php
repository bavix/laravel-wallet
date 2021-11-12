<?php

declare(strict_types=1);

namespace Bavix\Wallet;

use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssembler;
use Bavix\Wallet\Internal\BasketInterface;
use Bavix\Wallet\Internal\BookkeeperInterface;
use Bavix\Wallet\Internal\ConsistencyInterface;
use Bavix\Wallet\Internal\ExchangeInterface;
use Bavix\Wallet\Internal\LockInterface;
use Bavix\Wallet\Internal\MathInterface;
use Bavix\Wallet\Internal\PurchaseInterface;
use Bavix\Wallet\Internal\Service\TranslatorService;
use Bavix\Wallet\Internal\StorageInterface;
use Bavix\Wallet\Internal\TranslatorInterface;
use Bavix\Wallet\Internal\UuidInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
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

        if ($this->shouldMigrate()) {
            $this->loadMigrationsFrom([dirname(__DIR__).'/database']);
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

        $configure = config('wallet', []);

        $this->singletons($configure['services'] ?? []);
        $this->legacySingleton(); // without configuration
        $this->bindObjects($configure);
        $this->assemblers($configure['assemblers'] ?? []);
    }

    /**
     * Determine if we should register the migrations.
     */
    private function shouldMigrate(): bool
    {
        return WalletConfigure::isRunsMigrations();
    }

    private function singletons(array $configure): void
    {
        $this->app->singleton(BasketInterface::class, $configure['basket'] ?? BasketService::class);
        $this->app->singleton(BookkeeperInterface::class, $configure['bookkeeper'] ?? BookkeeperService::class);
        $this->app->singleton(ConsistencyInterface::class, $configure['consistency'] ?? ConsistencyService::class);
        $this->app->singleton(TranslatorInterface::class, $configure['translator'] ?? TranslatorService::class);
        $this->app->singleton(ExchangeInterface::class, $configure['exchange'] ?? ExchangeService::class);
        $this->app->singleton(LockInterface::class, $configure['atomic'] ?? AtomicService::class);
        $this->app->singleton(MathInterface::class, $configure['math'] ?? MathService::class);
        $this->app->singleton(PurchaseInterface::class, $configure['purchase'] ?? PurchaseService::class);
        $this->app->singleton(StorageInterface::class, $configure['storage'] ?? StorageService::class);
        $this->app->singleton(UuidInterface::class, $configure['uuid'] ?? UuidFactoryService::class);
    }

    private function assemblers(array $configure): void
    {
        $this->app->singleton(TransactionDtoAssembler::class, $configure['transaction'] ?? null);
        $this->app->singleton(TransferLazyDtoAssembler::class, $configure['transfer_lazy'] ?? null);
        $this->app->singleton(TransferDtoAssembler::class, $configure['transfer'] ?? null);
    }

    private function legacySingleton(): void
    {
        $this->app->singleton(CommonService::class);
        $this->app->singleton(WalletService::class);

        $this->app->singleton(DbService::class);
        $this->app->singleton(LockService::class);
        $this->app->singleton(MetaService::class);
    }

    private function bindObjects(array $configure): void
    {
        $this->app->bind(Transaction::class, $configure['transaction']['model'] ?? null);
        $this->app->bind(Transfer::class, $configure['transfer']['model'] ?? null);
        $this->app->bind(Wallet::class, $configure['wallet']['model'] ?? null);
    }
}
