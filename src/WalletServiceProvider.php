<?php

declare(strict_types=1);

namespace Bavix\Wallet;

use Bavix\Wallet\Commands\TransferFixCommand;
use Bavix\Wallet\External\Api\TransactionQueryHandler;
use Bavix\Wallet\External\Api\TransactionQueryHandlerInterface;
use Bavix\Wallet\External\Api\TransferQueryHandler;
use Bavix\Wallet\External\Api\TransferQueryHandlerInterface;
use Bavix\Wallet\Internal\Assembler\AvailabilityDtoAssembler;
use Bavix\Wallet\Internal\Assembler\AvailabilityDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\BalanceUpdatedEventAssembler;
use Bavix\Wallet\Internal\Assembler\BalanceUpdatedEventAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\ExtraDtoAssembler;
use Bavix\Wallet\Internal\Assembler\ExtraDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\OptionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\OptionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransactionCreatedEventAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionCreatedEventAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransactionQueryAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionQueryAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\TransferQueryAssembler;
use Bavix\Wallet\Internal\Assembler\TransferQueryAssemblerInterface;
use Bavix\Wallet\Internal\Assembler\WalletCreatedEventAssembler;
use Bavix\Wallet\Internal\Assembler\WalletCreatedEventAssemblerInterface;
use Bavix\Wallet\Internal\Decorator\StorageServiceLockDecorator;
use Bavix\Wallet\Internal\Events\BalanceUpdatedEvent;
use Bavix\Wallet\Internal\Events\BalanceUpdatedEventInterface;
use Bavix\Wallet\Internal\Events\TransactionCreatedEvent;
use Bavix\Wallet\Internal\Events\TransactionCreatedEventInterface;
use Bavix\Wallet\Internal\Events\WalletCreatedEvent;
use Bavix\Wallet\Internal\Events\WalletCreatedEventInterface;
use Bavix\Wallet\Internal\Repository\TransactionRepository;
use Bavix\Wallet\Internal\Repository\TransactionRepositoryInterface;
use Bavix\Wallet\Internal\Repository\TransferRepository;
use Bavix\Wallet\Internal\Repository\TransferRepositoryInterface;
use Bavix\Wallet\Internal\Repository\WalletRepository;
use Bavix\Wallet\Internal\Repository\WalletRepositoryInterface;
use Bavix\Wallet\Internal\Service\ClockService;
use Bavix\Wallet\Internal\Service\ClockServiceInterface;
use Bavix\Wallet\Internal\Service\ConnectionService;
use Bavix\Wallet\Internal\Service\ConnectionServiceInterface;
use Bavix\Wallet\Internal\Service\DatabaseService;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\DispatcherService;
use Bavix\Wallet\Internal\Service\DispatcherServiceInterface;
use Bavix\Wallet\Internal\Service\JsonService;
use Bavix\Wallet\Internal\Service\JsonServiceInterface;
use Bavix\Wallet\Internal\Service\LockService;
use Bavix\Wallet\Internal\Service\LockServiceInterface;
use Bavix\Wallet\Internal\Service\MathService;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Service\StateService;
use Bavix\Wallet\Internal\Service\StateServiceInterface;
use Bavix\Wallet\Internal\Service\StorageService;
use Bavix\Wallet\Internal\Service\StorageServiceInterface;
use Bavix\Wallet\Internal\Service\TranslatorService;
use Bavix\Wallet\Internal\Service\TranslatorServiceInterface;
use Bavix\Wallet\Internal\Service\UuidFactoryService;
use Bavix\Wallet\Internal\Service\UuidFactoryServiceInterface;
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
use Bavix\Wallet\Services\AtomicService;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\BasketService;
use Bavix\Wallet\Services\BasketServiceInterface;
use Bavix\Wallet\Services\BookkeeperService;
use Bavix\Wallet\Services\BookkeeperServiceInterface;
use Bavix\Wallet\Services\CastService;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\ConsistencyService;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use Bavix\Wallet\Services\DiscountService;
use Bavix\Wallet\Services\DiscountServiceInterface;
use Bavix\Wallet\Services\EagerLoaderService;
use Bavix\Wallet\Services\EagerLoaderServiceInterface;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\ExchangeServiceInterface;
use Bavix\Wallet\Services\PrepareService;
use Bavix\Wallet\Services\PrepareServiceInterface;
use Bavix\Wallet\Services\PurchaseService;
use Bavix\Wallet\Services\PurchaseServiceInterface;
use Bavix\Wallet\Services\RegulatorService;
use Bavix\Wallet\Services\RegulatorServiceInterface;
use Bavix\Wallet\Services\TaxService;
use Bavix\Wallet\Services\TaxServiceInterface;
use Bavix\Wallet\Services\TransactionService;
use Bavix\Wallet\Services\TransactionServiceInterface;
use Bavix\Wallet\Services\TransferService;
use Bavix\Wallet\Services\TransferServiceInterface;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Services\WalletServiceInterface;
use function config;
use function dirname;
use function function_exists;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionCommitting;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Support\Facades\Event;
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
        $this->loadTranslationsFrom(dirname(__DIR__) . '/resources/lang', 'wallet');

        Event::listen(TransactionBeginning::class, Internal\Listeners\TransactionBeginningListener::class);
        Event::listen(TransactionCommitting::class, Internal\Listeners\TransactionCommittingListener::class);
        Event::listen(TransactionCommitted::class, Internal\Listeners\TransactionCommittedListener::class);
        Event::listen(TransactionRolledBack::class, Internal\Listeners\TransactionRolledBackListener::class);

        if (! $this->app->runningInConsole()) {
            return;
        }

        if ($this->shouldMigrate()) {
            $this->loadMigrationsFrom([dirname(__DIR__) . '/database']);
        }

        if (function_exists('config_path')) {
            $this->publishes([
                dirname(__DIR__) . '/config/config.php' => config_path('wallet.php'),
            ], 'laravel-wallet-config');
        }

        $this->publishes([
            dirname(__DIR__) . '/database/' => database_path('migrations'),
        ], 'laravel-wallet-migrations');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__) . '/config/config.php', 'wallet');
        $this->commands([TransferFixCommand::class]);

        /**
         * @var array{
         *     internal?: array<class-string|null>,
         *     services?: array<class-string|null>,
         *     cache?: array{driver: string|null},
         *     repositories?: array<class-string|null>,
         *     transformers?: array<class-string|null>,
         *     assemblers?: array<class-string|null>,
         *     events?: array<class-string|null>,
         *     transaction?: array{model?: class-string|null},
         *     transfer?: array{model?: class-string|null},
         *     wallet?: array{model?: class-string|null},
         * } $configure
         */
        $configure = config('wallet', []);

        $this->internal($configure['internal'] ?? []);
        $this->services($configure['services'] ?? [], $configure['cache'] ?? []);

        $this->repositories($configure['repositories'] ?? []);
        $this->transformers($configure['transformers'] ?? []);
        $this->assemblers($configure['assemblers'] ?? []);
        $this->events($configure['events'] ?? []);

        $this->bindObjects($configure);
    }

    /**
     * @param array<class-string|null> $configure
     */
    private function repositories(array $configure): void
    {
        $this->app->singleton(
            TransactionRepositoryInterface::class,
            $configure['transaction'] ?? TransactionRepository::class
        );

        $this->app->singleton(
            TransferRepositoryInterface::class,
            $configure['transfer'] ?? TransferRepository::class
        );

        $this->app->singleton(WalletRepositoryInterface::class, $configure['wallet'] ?? WalletRepository::class);
    }

    /**
     * Determine if we should register the migrations.
     */
    private function shouldMigrate(): bool
    {
        return WalletConfigure::isRunsMigrations();
    }

    /**
     * @param array<class-string|null> $configure
     */
    private function internal(array $configure): void
    {
        $this->app->alias($configure['storage'] ?? StorageService::class, 'wallet.internal.storage');
        $this->app->when($configure['storage'] ?? StorageService::class)
            ->needs('$ttl')
            ->giveConfig('wallet.cache.ttl');

        $this->app->singleton(ClockServiceInterface::class, $configure['clock'] ?? ClockService::class);
        $this->app->singleton(ConnectionServiceInterface::class, $configure['connection'] ?? ConnectionService::class);
        $this->app->singleton(DatabaseServiceInterface::class, $configure['database'] ?? DatabaseService::class);
        $this->app->singleton(DispatcherServiceInterface::class, $configure['dispatcher'] ?? DispatcherService::class);
        $this->app->singleton(JsonServiceInterface::class, $configure['json'] ?? JsonService::class);

        $this->app->when($configure['lock'] ?? LockService::class)
            ->needs('$seconds')
            ->giveConfig('wallet.lock.seconds', 1);

        $this->app->singleton(LockServiceInterface::class, $configure['lock'] ?? LockService::class);

        $this->app->when($configure['math'] ?? MathService::class)
            ->needs('$scale')
            ->giveConfig('wallet.math.scale', 64);

        $this->app->singleton(MathServiceInterface::class, $configure['math'] ?? MathService::class);
        $this->app->singleton(StateServiceInterface::class, $configure['state'] ?? StateService::class);
        $this->app->singleton(TranslatorServiceInterface::class, $configure['translator'] ?? TranslatorService::class);
        $this->app->singleton(UuidFactoryServiceInterface::class, $configure['uuid'] ?? UuidFactoryService::class);
    }

    /**
     * @param array<class-string|null> $configure
     * @param array{driver?: string|null} $cache
     */
    private function services(array $configure, array $cache): void
    {
        $this->app->singleton(AssistantServiceInterface::class, $configure['assistant'] ?? AssistantService::class);
        $this->app->singleton(AtmServiceInterface::class, $configure['atm'] ?? AtmService::class);
        $this->app->singleton(AtomicServiceInterface::class, $configure['atomic'] ?? AtomicService::class);
        $this->app->singleton(BasketServiceInterface::class, $configure['basket'] ?? BasketService::class);
        $this->app->singleton(CastServiceInterface::class, $configure['cast'] ?? CastService::class);
        $this->app->singleton(
            ConsistencyServiceInterface::class,
            $configure['consistency'] ?? ConsistencyService::class
        );
        $this->app->singleton(DiscountServiceInterface::class, $configure['discount'] ?? DiscountService::class);
        $this->app->singleton(
            EagerLoaderServiceInterface::class,
            $configure['eager_loader'] ?? EagerLoaderService::class
        );
        $this->app->singleton(ExchangeServiceInterface::class, $configure['exchange'] ?? ExchangeService::class);
        $this->app->singleton(PrepareServiceInterface::class, $configure['prepare'] ?? PrepareService::class);
        $this->app->singleton(PurchaseServiceInterface::class, $configure['purchase'] ?? PurchaseService::class);
        $this->app->singleton(TaxServiceInterface::class, $configure['tax'] ?? TaxService::class);
        $this->app->singleton(
            TransactionServiceInterface::class,
            $configure['transaction'] ?? TransactionService::class
        );
        $this->app->singleton(TransferServiceInterface::class, $configure['transfer'] ?? TransferService::class);
        $this->app->singleton(WalletServiceInterface::class, $configure['wallet'] ?? WalletService::class);

        // bookkeepper service
        $this->app->when(StorageServiceLockDecorator::class)
            ->needs(StorageServiceInterface::class)
            ->give(function () use ($cache) {
                return $this->app->make(
                    'wallet.internal.storage',
                    [
                        'cacheRepository' => $this->app->get(CacheFactory::class)
                            ->store($cache['driver'] ?? 'array'),
                    ],
                );
            });

        $this->app->when($configure['bookkeeper'] ?? BookkeeperService::class)
            ->needs(StorageServiceInterface::class)
            ->give(StorageServiceLockDecorator::class);

        $this->app->singleton(BookkeeperServiceInterface::class, $configure['bookkeeper'] ?? BookkeeperService::class);

        // regulator service
        $this->app->when($configure['regulator'] ?? RegulatorService::class)
            ->needs(StorageServiceInterface::class)
            ->give(function () {
                return $this->app->make(
                    'wallet.internal.storage',
                    [
                        'cacheRepository' => clone $this->app->make(CacheFactory::class)
                            ->store('array'),
                    ],
                );
            });

        $this->app->singleton(RegulatorServiceInterface::class, $configure['regulator'] ?? RegulatorService::class);
    }

    /**
     * @param array<class-string|null> $configure
     */
    private function assemblers(array $configure): void
    {
        $this->app->singleton(
            AvailabilityDtoAssemblerInterface::class,
            $configure['availability'] ?? AvailabilityDtoAssembler::class
        );

        $this->app->singleton(
            BalanceUpdatedEventAssemblerInterface::class,
            $configure['balance_updated_event'] ?? BalanceUpdatedEventAssembler::class
        );

        $this->app->singleton(ExtraDtoAssemblerInterface::class, $configure['extra'] ?? ExtraDtoAssembler::class);

        $this->app->singleton(
            OptionDtoAssemblerInterface::class,
            $configure['option'] ?? OptionDtoAssembler::class
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

        $this->app->singleton(
            TransactionQueryAssemblerInterface::class,
            $configure['transaction_query'] ?? TransactionQueryAssembler::class
        );

        $this->app->singleton(
            TransferQueryAssemblerInterface::class,
            $configure['transfer_query'] ?? TransferQueryAssembler::class
        );

        $this->app->singleton(
            WalletCreatedEventAssemblerInterface::class,
            $configure['wallet_created_event'] ?? WalletCreatedEventAssembler::class
        );

        $this->app->singleton(
            TransactionCreatedEventAssemblerInterface::class,
            $configure['transaction_created_event'] ?? TransactionCreatedEventAssembler::class
        );
    }

    /**
     * @param array<class-string|null> $configure
     */
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

    /**
     * @param array<class-string|null> $configure
     */
    private function events(array $configure): void
    {
        $this->app->bind(
            BalanceUpdatedEventInterface::class,
            $configure['balance_updated'] ?? BalanceUpdatedEvent::class
        );

        $this->app->bind(
            WalletCreatedEventInterface::class,
            $configure['wallet_created'] ?? WalletCreatedEvent::class
        );

        $this->app->bind(
            TransactionCreatedEventInterface::class,
            $configure['transaction_created'] ?? TransactionCreatedEvent::class
        );
    }

    /**
     * @param array{
     *     transaction?: array{model?: class-string|null},
     *     transfer?: array{model?: class-string|null},
     *     wallet?: array{model?: class-string|null},
     * } $configure
     */
    private function bindObjects(array $configure): void
    {
        $this->app->bind(Transaction::class, $configure['transaction']['model'] ?? null);
        $this->app->bind(Transfer::class, $configure['transfer']['model'] ?? null);
        $this->app->bind(Wallet::class, $configure['wallet']['model'] ?? null);

        // api
        $this->app->bind(TransactionQueryHandlerInterface::class, TransactionQueryHandler::class);
        $this->app->bind(TransferQueryHandlerInterface::class, TransferQueryHandler::class);
    }
}
