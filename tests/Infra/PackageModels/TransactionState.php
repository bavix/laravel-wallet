<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\PackageModels;

use Override;

/**
 * @property null|string $final_balance
 * @property null|string $checksum
 */
final class TransactionState extends \Bavix\Wallet\Models\Transaction
{
    #[Override]
    public function getFillable(): array
    {
        return array_merge($this->fillable, ['final_balance', 'checksum']);
    }
}
