<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\PackageModels;

use Override;

/**
 * @property null|string $balance_before
 * @property null|string $balance_after
 * @property null|string $state_hash
 */
final class TransactionState extends \Bavix\Wallet\Models\Transaction
{
    #[Override]
    public function getFillable(): array
    {
        return array_merge($this->fillable, ['balance_before', 'balance_after', 'state_hash']);
    }
}
