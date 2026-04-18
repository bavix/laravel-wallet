<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Projectors;

use Bavix\Wallet\Internal\Projector\WalletBatchProjectorInterface;

final readonly class WalletStateBatchProjector implements WalletBatchProjectorInterface
{
    public function project(array $balances, array $walletsById): array
    {
        $rows = [];
        foreach ($balances as $walletId => $resultingBalance) {
            $wallet = $walletsById[$walletId] ?? null;
            if ($wallet === null) {
                continue;
            }

            $heldBalance = (string) ($wallet->getAttribute('held_balance') ?? '0');
            $rows[$walletId] = [
                'balance_after' => $resultingBalance,
                'held_balance' => $heldBalance,
                'state_hash' => hash('sha256', $wallet->uuid.':'.$resultingBalance.':'.$heldBalance),
            ];
        }

        return $rows;
    }
}
