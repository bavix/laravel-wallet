<?php

namespace Bavix\Wallet\Commands;

use Bavix\Wallet\Models\Wallet;
use Illuminate\Console\Command;

/**
 * Class RefreshBalance.
 */
class RefreshBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wallet:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates all wallets';

    /**
     * @throws
     */
    public function handle(): void
    {
        Wallet::query()->each(static fn (Wallet $wallet) => $wallet->refreshBalance());
    }
}
