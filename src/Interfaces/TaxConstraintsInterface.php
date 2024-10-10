<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\External\Enums\TransactionType;

interface TaxConstraintsInterface extends TaxInterface
{
    public function getMinimumTax(TransactionType $enum): float|int|null;

    public function getMaximumTax(TransactionType $enum): float|int|null;
}
