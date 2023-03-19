<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\PackageModels;

/**
 * Class Transaction.
 *
 * @property null|string $bank_method
 */
final class Transaction extends \Bavix\Wallet\Models\Transaction
{
    public function getFillable(): array
    {
        return array_merge($this->fillable, ['bank_method']);
    }
}
