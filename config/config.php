<?php

declare(strict_types=1);

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\AtomicService;
use Bavix\Wallet\Services\BasketService;
use Bavix\Wallet\Services\BookkeeperService;
use Bavix\Wallet\Services\ConsistencyService;
use Bavix\Wallet\Services\ExchangeService;
use Bavix\Wallet\Services\MathService;
use Bavix\Wallet\Services\PurchaseService;
use Bavix\Wallet\Services\StorageService;
use Bavix\Wallet\Services\UuidFactoryService;

return [
    /**
     * Arbitrary Precision Calculator.
     *
     *  'scale' - length of the mantissa
     */
    'math' => ['scale' => 64],

    /**
     * Storage of the state of the balance of wallets.
     */
    'cache' => ['driver' => 'array'],

    /**
     * A system for dealing with race conditions.
     */
    'lock' => [
        'driver' => null,
        'seconds' => 1,
    ],

    /**
     * Services that can be overloaded.
     */
    'services' => [
        'basket' => BasketService::class,
        'bookkeeper' => BookkeeperService::class,
        'consistency' => ConsistencyService::class,
        'exchange' => ExchangeService::class,
        'atomic' => AtomicService::class,
        'math' => MathService::class,
        'purchase' => PurchaseService::class,
        'storage' => StorageService::class,
        'uuid' => UuidFactoryService::class,
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
