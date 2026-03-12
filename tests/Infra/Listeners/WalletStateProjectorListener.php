<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\Listeners;

use Bavix\Wallet\Test\Infra\PackageModels\Wallet;
use Illuminate\Database\Events\TransactionCommitting;
use Illuminate\Support\Facades\DB;

final readonly class WalletStateProjectorListener
{
    public function handle(TransactionCommitting $event): void
    {
        if (DB::connection($event->connectionName)->transactionLevel() !== 1) {
            return;
        }

        Wallet::query()->chunkById(100, static function ($wallets): void {
            foreach ($wallets as $wallet) {
                $finalBalance = $wallet->balance;
                $frozenBalance = (string) ($wallet->frozen_balance ?? '0');

                $wallet->forceFill([
                    'final_balance' => $finalBalance,
                    'frozen_balance' => $frozenBalance,
                    'checksum' => hash('sha256', $wallet->uuid.':'.$finalBalance.':'.$frozenBalance),
                ])->saveQuietly();
            }
        });
    }
}
