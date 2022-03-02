<?php

declare(strict_types=1);

use Bavix\Wallet\Internal\Assembler\AvailabilityDtoAssembler;
use Bavix\Wallet\Internal\Assembler\BalanceUpdatedEventAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransactionQueryAssembler;
use Bavix\Wallet\Internal\Assembler\TransferDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferLazyDtoAssembler;
use Bavix\Wallet\Internal\Assembler\TransferQueryAssembler;
use Bavix\Wallet\Internal\Events\BalanceUpdatedEvent;
use Bavix\Wallet\Internal\Events\WalletCreatedEvent;
use Bavix\Wallet\Internal\Repository\TransactionRepository;
use Bavix\Wallet\Internal\Repository\TransferRepository;
use Bavix\Wallet\Internal\Repository\WalletRepository;
use Bavix\Wallet\Internal\Service\ClockService;
use Bavix\Wallet\Internal\Service\DatabaseService;
use Bavix\Wallet\Internal\Service\DispatcherService;
use Bavix\Wallet\Internal\Service\JsonService;
use Bavix\Wallet\Internal\Service\LockService;
use Bavix\Wallet\Internal\Service\MathService;
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
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\PrepareService;
use Bavix\Wallet\Services\PurchaseService;
use Bavix\Wallet\Services\RegulatorService;
use Bavix\Wallet\Services\TaxService;
use Bavix\Wallet\Services\WalletService;

return [
    /**
     * Arbitrary Precision Calculator.
     *
     * 'scale' - length of the mantissa
     */
    'math' => [
        'scale' => 64,
    ],

    /**
     * Storage of the state of the balance of wallets.
     */
    'cache' => [
        'driver' => 'array',
    ],

    /**
     * A system for dealing with race conditions.
     */
    'lock' => [
        'driver' => 'array',
        'seconds' => 1,
    ],

    /**
     * Internal services that can be overloaded.
     */
    'internal' => [
        'clock' => ClockService::class,
        'database' => DatabaseService::class,
        'dispatcher' => DispatcherService::class,
        'json' => JsonService::class,
        'lock' => LockService::class,
        'math' => MathService::class,
        'storage' => StorageService::class,
        'translator' => TranslatorService::class,
        'uuid' => UuidFactoryService::class,
    ],

    /**
     * Services that can be overloaded.
     */
    'services' => [
        'assistant' => AssistantService::class,
        'atm' => AtmService::class,
        'atomic' => AtomicService::class,
        'basket' => BasketService::class,
        'bookkeeper' => BookkeeperService::class,
        'regulator' => RegulatorService::class,
        'cast' => CastService::class,
        'consistency' => ConsistencyService::class,
        'discount' => DiscountService::class,
        'exchange' => ExchangeService::class,
        'prepare' => PrepareService::class,
        'purchase' => PurchaseService::class,
        'tax' => TaxService::class,
        'wallet' => WalletService::class,
    ],

    /**
     * Repositories for fetching data from the database.
     */
    'repositories' => [
        'transaction' => TransactionRepository::class,
        'transfer' => TransferRepository::class,
        'wallet' => WalletRepository::class,
    ],

    /**
     * Objects of transformer from DTO to array.
     */
    'transformers' => [
        'transaction' => TransactionDtoTransformer::class,
        'transfer' => TransferDtoTransformer::class,
    ],

    /**
     * Builder class, needed to create DTO.
     */
    'assemblers' => [
        'availability' => AvailabilityDtoAssembler::class,
        'balance_updated_event' => BalanceUpdatedEventAssembler::class,
        'transaction' => TransactionDtoAssembler::class,
        'transfer_lazy' => TransferLazyDtoAssembler::class,
        'transfer' => TransferDtoAssembler::class,
        'transaction_query' => TransactionQueryAssembler::class,
        'transfer_query' => TransferQueryAssembler::class,
    ],

    /**
     * Package system events.
     */
    'events' => [
        'balance_updated' => BalanceUpdatedEvent::class,
        'wallet_created' => WalletCreatedEvent::class,
    ],

    /**
     * Base model 'transaction'.
     */
    'transaction' => [
        'table' => 'transactions',
        'model' => Transaction::class,
    ],

    /**
     * Base model 'transfer'.
     */
    'transfer' => [
        'table' => 'transfers',
        'model' => Transfer::class,
    ],

    /**
     * Base model 'wallet'.
     */
    'wallet' => [
        'table' => 'wallets',
        'model' => Wallet::class,
        'creating' => [],
        'default' => [
            'name' => 'Default Wallet',
            'slug' => 'default',
            'meta' => [],
        ],
    ],
];
