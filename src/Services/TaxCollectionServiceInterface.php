<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\External\Enums\TransactionType;
use Bavix\Wallet\Interfaces\Wallet;

interface TaxCollectionServiceInterface
{
    public function calculate(TransactionType $type, Wallet $wallet, float|int|string $amount): string;
}
