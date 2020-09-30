<?php

namespace Bavix\Wallet\Commands;

use Bavix\Wallet\Models\Wallet;
use Bavix\Wallet\Services\DbService;
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
     * @return void
     * @throws
     */
    public function handle(): void
    {
        app(DbService::class)->transaction(static function () {
            Wallet::query()->each(static function (Wallet $wallet) {
                $wallet->refreshBalance();
            });
        });
    }
}
