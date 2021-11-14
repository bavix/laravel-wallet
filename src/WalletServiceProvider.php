<?php

declare(strict_types=1);

namespace Bavix\Wallet;

use Bavix\Wallet\Internal\Assembler\AvailabilityDtoAssembler;
use Bavix\Wallet\Internal\Assembler\AvailabilityDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssemblerInterface;
use Bavix\Wallet\Internal\Repository\TransactionRepository;
use Bavix\Wallet\Internal\Repository\TransactionRepositoryInterface;
use Bavix\Wallet\Internal\Repository\TransferRepository;
use Bavix\Wallet\Internal\Repository\TransferRepositoryInterface;
use Bavix\Wallet\Internal\Service\DatabaseService;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\JsonService;
use Bavix\Wallet\Internal\Service\JsonServiceInterface;
use Bavix\Wallet\Internal\Service\LockService as NewLockService;
use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Internal\Service\MathService;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Service\StorageService;
use Bavix\Wallet\Internal\Service\StorageServiceInterface;
use Bavix\Wallet\Internal\Service\TranslatorService;
use Bavix\Wallet\Internal\Service\TranslatorServiceInterface;
use Bavix\Wallet\Internal\Service\UuidService;
use Bavix\Wallet\Internal\Service\UuidServiceInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Bavix\Wallet\Internal\Transform\TransferDtoTransformer;
use Bavix\Wallet\Internal\Transform\TransferDtoTransformerInterface;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\AssistantService;
use Bavix\Wallet\Services\AssistantServiceInterface;
use Bavix\Wallet\Services\AtmService;
use Bavix\Wallet\Services\AtmServiceInterface;
use Bavix\Wallet\Services\AtomicKeyService;
use Bavix\Wallet\Services\AtomicKeyServiceInterface;
use Bavix\Wallet\Services\BasketService;
use Bavix\Wallet\Services\BasketServiceInterface;
use Bavix\Wallet\Services\BookkeeperService;
use Bavix\Wallet\Services\BookkeeperServiceInterface;
use Bavix\Wallet\Services\CastService;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\CommonServiceLegacy;
use Bavix\Wallet\Services\ConsistencyService;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\DiscountService;
use Bavix\Wallet\Services\DiscountServiceInterface;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\ExchangeServiceInterface;
use Bavix\Wallet\Services\MetaServiceLegacy;
use Bavix\Wallet\Services\PrepareService;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\PurchaseService;
use Bavix\Wallet\Services\PurchaseServiceInterface;
use Bavix\Wallet\Services\TaxService;
use Bavix\Wallet\Services\TaxServiceInterface;
use Bavix\Wallet\Services\WalletServiceLegacy;
use function config;
use function dirname;
use function function_exists;
use Illuminate\Support\ServiceProvider;

final class WalletServiceProvider extends ServiceProvider
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

        $this->internal($configure['internal'] ?? []);
        $this->services($configure['services'] ?? []);
        $this->legacySingleton(); // without configuration

        $this->repositories($configure['repositories'] ?? []);
        $this->transformers($configure['transformers'] ?? []);
        $this->assemblers($configure['assemblers'] ?? []);

        $this->bindObjects($configure);
    }

    public function repositories(array $configure): void
    {
        $this->app->singleton(
            TransactionRepositoryInterface::class,
            $configure['transaction'] ?? TransactionRepository::class
        );

        $this->app->singleton(
            TransferRepositoryInterface::class,
            $configure['transfer'] ?? TransferRepository::class
        );
    }

    /**
     * Determine if we should register the migrations.
     */
    private function shouldMigrate(): bool
    {
        return WalletConfigure::isRunsMigrations();
    }

    private function internal(array $configure): void
    {
        $this->app->singleton(DatabaseServiceInterface::class, $configure['database'] ?? DatabaseService::class);
        $this->app->singleton(JsonServiceInterface::class, $configure['json'] ?? JsonService::class);
        $this->app->singleton(LockServiceInterface::class, $configure['lock'] ?? NewLockService::class);
        $this->app->singleton(MathServiceInterface::class, $configure['math'] ?? MathService::class);
        $this->app->singleton(StorageServiceInterface::class, $configure['storage'] ?? StorageService::class);
        $this->app->singleton(TranslatorServiceInterface::class, $configure['translator'] ?? TranslatorService::class);
        $this->app->singleton(UuidServiceInterface::class, $configure['uuid'] ?? UuidService::class);
    }

    private function services(array $configure): void
    {
        $this->app->singleton(AssistantServiceInterface::class, $configure['assistant'] ?? AssistantService::class);
        $this->app->singleton(AtmServiceInterface::class, $configure['atm'] ?? AtmService::class);
        $this->app->singleton(AtomicKeyServiceInterface::class, $configure['key'] ?? AtomicKeyService::class);
        $this->app->singleton(BasketServiceInterface::class, $configure['basket'] ?? BasketService::class);
        $this->app->singleton(BookkeeperServiceInterface::class, $configure['bookkeeper'] ?? BookkeeperService::class);
        $this->app->singleton(CastServiceInterface::class, $configure['cast'] ?? CastService::class);
        $this->app->singleton(ConsistencyServiceInterface::class, $configure['consistency'] ?? ConsistencyService::class);
        $this->app->singleton(DiscountServiceInterface::class, $configure['discount'] ?? DiscountService::class);
        $this->app->singleton(ExchangeServiceInterface::class, $configure['exchange'] ?? ExchangeService::class);
        $this->app->singleton(PrepareServiceInterface::class, $configure['prepare'] ?? PrepareService::class);
        $this->app->singleton(PurchaseServiceInterface::class, $configure['purchase'] ?? PurchaseService::class);
        $this->app->singleton(TaxServiceInterface::class, $configure['tax'] ?? TaxService::class);
    }

    private function assemblers(array $configure): void
    {
        $this->app->singleton(
            AvailabilityDtoAssemblerInterface::class,
            $configure['availability'] ?? AvailabilityDtoAssembler::class
        );

        $this->app->singleton(
            TransactionDtoAssemblerInterface::class,
            $configure['transaction'] ?? TransactionDtoAssembler::class
        );

        $this->app->singleton(
            TransferLazyDtoAssemblerInterface::class,
            $configure['transfer_lazy'] ?? TransferLazyDtoAssembler::class
        );

        $this->app->singleton(
            TransferDtoAssemblerInterface::class,
            $configure['transfer'] ?? TransferDtoAssembler::class
        );
    }

    private function transformers(array $configure): void
    {
        $this->app->singleton(
            TransactionDtoTransformerInterface::class,
            $configure['transaction'] ?? TransactionDtoTransformer::class
        );

        $this->app->singleton(
            TransferDtoTransformerInterface::class,
            $configure['transfer'] ?? TransferDtoTransformer::class
        );
    }

    private function legacySingleton(): void
    {
        $this->app->singleton(CommonServiceLegacy::class);
        $this->app->singleton(WalletServiceLegacy::class);
        $this->app->singleton(MetaServiceLegacy::class);
    }

    private function bindObjects(array $configure): void
    {
        $this->app->bind(Transaction::class, $configure['transaction']['model'] ?? null);
        $this->app->bind(Transfer::class, $configure['transfer']['model'] ?? null);
        $this->app->bind(Wallet::class, $configure['wallet']['model'] ?? null);
    }
}
