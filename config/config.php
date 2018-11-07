<?php

return [
    'transaction' => [
        'table' => 'transactions',
        'model' => \Bavix\Wallet\Models\Transaction::class,
    ],
    'transfer' => [
        'table' => 'transfers',
        'model' => \Bavix\Wallet\Models\Transfer::class,
    ],
];
