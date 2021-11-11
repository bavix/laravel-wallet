<?php

declare(strict_types=1);

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

return [
    // infra settings
    'cache' => ['driver' => null],
    'lock' => [
        'driver' => null,
        'seconds' => 1,
    ],

    // long arithmetic
    'math' => ['scale' => 64],

    // service overload
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

    // legacy-service overload
    'legacy' => [
        'common' => CommonService::class,
        'wallet' => WalletService::class,
        'db' => DbService::class,
        'lock' => LockService::class,
        'meta' => MetaService::class,
    ],

    'transaction' => [
        'table' => 'transactions',
        'model' => Transaction::class,
    ],

    'transfer' => [
        'table' => 'transfers',
        'model' => Transfer::class,
    ],

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
