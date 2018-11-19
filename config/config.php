<?php

return [
    'package' => [
        'coefficient' => 100.,
    ],
    'transaction' => [
        'table' => 'transactions',
        'model' => \Bavix\Wallet\Models\Transaction::class,
    ],
    'transfer' => [
        'table' => 'transfers',
        'model' => \Bavix\Wallet\Models\Transfer::class,
    ],
    'wallet' => [
        'table' => 'wallets',
        'model' => \Bavix\Wallet\Models\Wallet::class,
        'data' => [
            'name' => 'Default Wallet',
            'slug' => 'default',
        ],
    ],
];
