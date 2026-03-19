<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\PackageModels;

use Override;

/**
 * @property null|string $held_balance
 * @property null|string $balance_after
 * @property null|string $state_hash
 */
final class WalletState extends \Bavix\Wallet\Models\Wallet
{
    #[Override]
    public function getFillable(): array
    {
        return array_merge($this->fillable, ['held_balance', 'balance_after', 'state_hash']);
    }
}
