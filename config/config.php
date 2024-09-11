<?php

declare(strict_types=1);

use Bavix\Wallet\Internal\Assembler\AvailabilityDtoAssembler;
use Bavix\Wallet\Internal\Assembler\BalanceUpdatedEventAssembler;
use Bavix\Wallet\Internal\Assembler\ExtraDtoAssembler;
use Bavix\Wallet\Internal\Assembler\OptionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionCreatedEventAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionQueryAssembler;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferQueryAssembler;
use Bavix\Wallet\Internal\Events\BalanceUpdatedEvent;
use Bavix\Wallet\Internal\Events\TransactionCreatedEvent;
use Bavix\Wallet\Internal\Events\WalletCreatedEvent;
use Bavix\Wallet\Internal\Repository\TransactionRepository;
use Bavix\Wallet\Internal\Repository\TransferRepository;
use Bavix\Wallet\Internal\Repository\WalletRepository;
use Bavix\Wallet\Internal\Service\ClockService;
use Bavix\Wallet\Internal\Service\ConnectionService;
use Bavix\Wallet\Internal\Service\DatabaseService;
use Bavix\Wallet\Internal\Service\DispatcherService;
use Bavix\Wallet\Internal\Service\IdentifierFactoryService;
use Bavix\Wallet\Internal\Service\JsonService;
use Bavix\Wallet\Internal\Service\LockService;
use Bavix\Wallet\Internal\Service\MathService;
use Bavix\Wallet\Internal\Service\StateService;
use Bavix\Wallet\Internal\Service\StorageService;
use Bavix\Wallet\Internal\Service\TranslatorService;
use Bavix\Wallet\Internal\Service\UuidFactoryService;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformer;
use Bavix\Wallet\Internal\Transform\TransferDtoTransformer;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\AssistantService;
use Bavix\Wallet\Services\AtmService;
use Bavix\Wallet\Services\AtomicService;
use Bavix\Wallet\Services\BasketService;
use Bavix\Wallet\Services\BookkeeperService;
use Bavix\Wallet\Services\CastService;
use Bavix\Wallet\Services\ConsistencyService;
use Bavix\Wallet\Services\DiscountService;
use Bavix\Wallet\Services\EagerLoaderService;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\FormatterService;
use Bavix\Wallet\Services\PrepareService;
use Bavix\Wallet\Services\PurchaseService;
use Bavix\Wallet\Services\RegulatorService;
use Bavix\Wallet\Services\TaxService;
use Bavix\Wallet\Services\TransactionService;
use Bavix\Wallet\Services\TransferService;
use Bavix\Wallet\Services\WalletService;

return [
    /**
     * Arbitrary Precision Calculator.
     *
     * The 'scale' option defines the number of decimal places
     * that the calculator will use when performing calculations.
     *
     * @see MathService
     */
    'math' => [
        /**
         * The scale of the calculator.
         *
         * @var int
         */
        'scale' => env('WALLET_MATH_SCALE', 64),
    ],

    /**
     * Storage of the state of the balance of wallets.
     *
     * This is used to cache the results of calculations
     * in order to improve the performance of the package.
     *
     * @see StorageService
     */
    'cache' => [
        /**
         * The driver for the cache.
         *
         * @var string
         */
        'driver' => env('WALLET_CACHE_DRIVER', 'array'),

        /**
         * The time to live for the cache in seconds.
         *
         * @var int
         */
        'ttl' => env('WALLET_CACHE_TTL', 24 * 3600),
    ],

    /**
     * A system for dealing with race conditions.
     *
     * This is used to protect against race conditions
     * when updating the balance of a wallet.
     *
     * @see LockService
     */
    'lock' => [
        /**
         * The driver for the lock.
         *
         * The following drivers are supported:
         * - array
         * - redis
         * - memcached
         * - database
         *
         * @var string
         */
        'driver' => env('WALLET_LOCK_DRIVER', 'array'),

        /**
         * The time to live for the lock in seconds.
         *
         * @var int
         */
        'seconds' => env('WALLET_LOCK_TTL', 1),
    ],

    /**
     * Internal services that can be overloaded.
     *
     * This section contains the list of services that can be overloaded by
     * the user. These services are used internally by the package and are
     * critical for it to function properly.
     *
     * @var array<string, class-string>
     */
    'internal' => [
        /**
         * The service for getting the current time.
         *
         * @var string
         */
        'clock' => ClockService::class,

        /**
         * The service for getting the database connection.
         *
         * @var string
         */
        'connection' => ConnectionService::class,

        /**
         * The service for managing the database.
         *
         * @var string
         */
        'database' => DatabaseService::class,

        /**
         * The service for dispatching events.
         *
         * @var string
         */
        'dispatcher' => DispatcherService::class,

        /**
         * The service for serializing and deserializing JSON.
         *
         * @var string
         */
        'json' => JsonService::class,

        /**
         * The service for handling locks.
         *
         * @var string
         */
        'lock' => LockService::class,

        /**
         * The service for performing mathematical operations.
         *
         * @var string
         */
        'math' => MathService::class,

        /**
         * The service for managing the state of the wallet.
         *
         * @var string
         */
        'state' => StateService::class,

        /**
         * The service for managing the storage of the wallet.
         *
         * @var string
         */
        'storage' => StorageService::class,

        /**
         * The service for translating messages.
         *
         * @var string
         */
        'translator' => TranslatorService::class,

        /**
         * The service for generating UUIDs.
         *
         * @var string
         *
         * @deprecated use identifier.
         * @see IdentifierFactoryService
         */
        'uuid' => UuidFactoryService::class,

        /**
         * The service for generating identifiers.
         *
         * @var string
         */
        'identifier' => IdentifierFactoryService::class,
    ],

    /**
     * Services that can be overloaded.
     *
     * Each key is the name of the service, and the value is the fully qualified class name of the service.
     * The default service class is provided here.
     *
     * @var array<string, class-string>
     *
     * @see \Bavix\Wallet\Services
     */
    'services' => [
        // Service for performing operations related to the assistant.
        'assistant' => AssistantService::class,
        // Service for handling ATM operations.
        'atm' => AtmService::class,
        // Service for handling atomic operations.
        'atomic' => AtomicService::class,
        // Service for managing the user's basket.
        'basket' => BasketService::class,
        // Service for handling bookkeeping operations.
        'bookkeeper' => BookkeeperService::class,
        // Service for handling regulation operations.
        'regulator' => RegulatorService::class,
        // Service for casting values.
        'cast' => CastService::class,
        // Service for handling consistency operations.
        'consistency' => ConsistencyService::class,
        // Service for handling discount operations.
        'discount' => DiscountService::class,
        // Service for handling eager loading operations.
        'eager_loader' => EagerLoaderService::class,
        // Service for handling exchange operations.
        'exchange' => ExchangeService::class,
        // Service for formatting values.
        'formatter' => FormatterService::class,
        // Service for preparing operations.
        'prepare' => PrepareService::class,
        // Service for handling purchase operations.
        'purchase' => PurchaseService::class,
        // Service for handling tax operations.
        'tax' => TaxService::class,
        // Service for handling transaction operations.
        'transaction' => TransactionService::class,
        // Service for handling transfer operations.
        'transfer' => TransferService::class,
        // Service for managing wallet operations.
        'wallet' => WalletService::class,
    ],

    /**
     * Repositories for fetching data from the database.
     *
     * Each repository is responsible for fetching data from the database for a specific entity.
     *
     * @see \Bavix\Wallet\Interfaces\Wallet
     * @see \Bavix\Wallet\Interfaces\Transaction
     * @see \Bavix\Wallet\Interfaces\Transfer
     */
    'repositories' => [
        /**
         * Repository for fetching transaction data.
         *
         * @see \Bavix\Wallet\Interfaces\Transaction
         */
        'transaction' => TransactionRepository::class,
        /**
         * Repository for fetching transfer data.
         *
         * @see \Bavix\Wallet\Interfaces\Transfer
         */
        'transfer' => TransferRepository::class,
        /**
         * Repository for fetching wallet data.
         *
         * @see \Bavix\Wallet\Interfaces\Wallet
         */
        'wallet' => WalletRepository::class,
    ],

    /**
     * Defines the mapping of DTO (Data Transfer Object) types to their respective transformer classes.
     * Transformers are used to convert DTOs into a structured array format, suitable for further processing
     * or output. This allows for a clean separation between the internal data representation and the format
     * required by clients or external systems.
     */
    'transformers' => [
        /**
         * Transformer for converting transaction DTOs.
         * This transformer handles the conversion of transaction data, ensuring that all necessary
         * information is presented in a structured and consistent manner for downstream processing.
         */
        'transaction' => TransactionDtoTransformer::class,

        /**
         * Transformer for converting transfer DTOs.
         * Similar to the transaction transformer, this class is responsible for taking transfer-related
         * DTOs and converting them into a standardized array format. This is essential for operations
         * involving the movement of funds or assets between accounts or entities.
         */
        'transfer' => TransferDtoTransformer::class,
    ],

    /**
     * Builder class, needed to create DTO.
     */
    'assemblers' => [
        /**
         * Assembler for creating Availability DTO.
         */
        'availability' => AvailabilityDtoAssembler::class,
        /**
         * Assembler for creating Balance Updated Event DTO.
         */
        'balance_updated_event' => BalanceUpdatedEventAssembler::class,
        /**
         * Assembler for creating Extra DTO.
         */
        'extra' => ExtraDtoAssembler::class,
        /**
         * Assembler for creating Option DTO.
         */
        'option' => OptionDtoAssembler::class,
        /**
         * Assembler for creating Transaction DTO.
         */
        'transaction' => TransactionDtoAssembler::class,
        /**
         * Assembler for creating Transfer Lazy DTO.
         */
        'transfer_lazy' => TransferLazyDtoAssembler::class,
        /**
         * Assembler for creating Transfer DTO.
         */
        'transfer' => TransferDtoAssembler::class,
        /**
         * Assembler for creating Transaction Created Event DTO.
         */
        'transaction_created_event' => TransactionCreatedEventAssembler::class,
        /**
         * Assembler for creating Transaction Query DTO.
         */
        'transaction_query' => TransactionQueryAssembler::class,
        /**
         * Assembler for creating Transfer Query DTO.
         */
        'transfer_query' => TransferQueryAssembler::class,
    ],

    /**
     * Package system events.
     *
     * @var array<string, class-string>
     */
    'events' => [
        /**
         * The event triggered when the balance is updated.
         */
        'balance_updated' => BalanceUpdatedEvent::class,

        /**
         * The event triggered when a wallet is created.
         */
        'wallet_created' => WalletCreatedEvent::class,

        /**
         * The event triggered when a transaction is created.
         */
        'transaction_created' => TransactionCreatedEvent::class,
    ],

    /**
     * Base model 'transaction'.
     *
     * @see Transaction
     */
    'transaction' => [
        /**
         * The table name for transactions.
         *
         * This value is used to store transactions in a database.
         *
         * @see Transaction
         */
        'table' => env('WALLET_TRANSACTION_TABLE_NAME', 'transactions'),

        /**
         * The model class for transactions.
         *
         * This value is used to create new transactions.
         *
         * @see Transaction
         */
        'model' => Transaction::class,
    ],

    /**
     * Base model 'transfer'.
     *
     * Contains the configuration for the transfer model.
     *
     * @see Transfer
     */
    'transfer' => [
        /**
         * The table name for transfers.
         *
         * This value is used to store transfers in a database.
         *
         * @see Transfer
         */
        'table' => env('WALLET_TRANSFER_TABLE_NAME', 'transfers'),

        /**
         * The model class for transfers.
         *
         * This value is used to create new transfers.
         *
         * @see Transfer
         */
        'model' => Transfer::class,
    ],

    /**
     * Base model 'wallet'.
     *
     * Contains the configuration for the wallet model.
     *
     * @see Wallet
     */
    'wallet' => [
        /**
         * The table name for wallets.
         *
         * This value is used to store wallets in a database.
         *
         * @see Wallet
         */
        'table' => env('WALLET_WALLET_TABLE_NAME', 'wallets'),

        /**
         * The model class for wallets.
         *
         * This value is used to create new wallets.
         *
         * @see Wallet
         */
        'model' => Wallet::class,

        /**
         * The configuration options for creating wallets.
         *
         * @var array<string, mixed>
         */
        'creating' => [],

        /**
         * The default configuration for wallets.
         *
         * @var array<string, mixed>
         */
        'default' => [
            /**
             * The name of the default wallet.
             *
             * @var string
             */
            'name' => env('WALLET_DEFAULT_WALLET_NAME', 'Default Wallet'),

            /**
             * The slug of the default wallet.
             *
             * @var string
             */
            'slug' => env('WALLET_DEFAULT_WALLET_SLUG', 'default'),

            /**
             * The meta information of the default wallet.
             *
             * @var array<string, mixed>
             */
            'meta' => [],
        ],
    ],
];
