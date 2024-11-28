<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\External\Enums\TransactionType;

interface TaxInterface
{
    public function getTaxPercent(TransactionType $enum): float|int;
}
