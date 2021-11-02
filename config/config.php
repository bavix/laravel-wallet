<?php

use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Objects\Bring;
use Bavix\Wallet\Objects\Cart;
use Bavix\Wallet\Objects\Operation;
use Bavix\Wallet\Services\CommonService;
use Bavix\Wallet\Services\LockService;
use Bavix\Wallet\Services\MathService;
use Bavix\Wallet\Services\WalletService;
use Bavix\Wallet\Simple\Exchange;

return [
    /**
     * This parameter is necessary for more accurate calculations.
     * PS, Arbitrary Precision Calculations.
     */
    'math' => [
        'scale' => 64,
    ],

    /**
     * The parameter is used for fast packet overload.
     * You do not need to search for the desired class by code, the library will do it itself.
     */
    'package' => [
        'exchange' => Exchange::class,
        'mathable' => MathService::class,
    ],

    /**
     * Lock settings for highload projects.
     *
     * If you want to replace the default cache with another,
     * then write the name of the driver cache in the key `wallet.lock.cache`.
     * @see https://laravel.com/docs/6.x/cache#driver-prerequisites
     *
     * @example
     *  'cache' => 'redis'
     */
    'lock' => [
        'cache' => null,
        'enabled' => false,
        'seconds' => 1,
    ],

    /**
     * Services are the main core of the library and sometimes they need to be improved.
     * This configuration will help you to quickly customize the library.
     */
    'services' => [
        'common' => CommonService::class,
        'wallet' => WalletService::class,
        'lock' => LockService::class,
    ],

    'objects' => [
        'bring' => Bring::class,
        'cart' => Cart::class,
        'operation' => Operation::class,
    ],

    /**
     * Transaction model configuration.
     */
    'transaction' => [
        'table' => 'transactions',
        'model' => Transaction::class,
        'casts' => [
            'amount' => 'string',
        ],
    ],

    /**
     * Transfer model configuration.
     */
    'transfer' => [
        'table' => 'transfers',
        'model' => Transfer::class,
        'casts' => [
            'fee' => 'string',
        ],
    ],

    /**
     * Wallet model configuration.
     */
    'wallet' => [
        'table' => 'wallets',
        'model' => Wallet::class,
        'casts' => [
            'balance' => 'string',
        ],
        'creating' => [],
        'default' => [
            'name' => 'Default Wallet',
            'slug' => 'default',
            'meta' => [],
        ],
    ],
];
