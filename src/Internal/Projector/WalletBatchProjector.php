<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Projector;

final readonly class WalletBatchProjector implements WalletBatchProjectorInterface
{
    public function project(array $balances, array $walletsById): array
    {
        return [];
    }
}
