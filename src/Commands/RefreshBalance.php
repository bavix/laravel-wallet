<?php

namespace Bavix\Wallet\Commands;

use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use function config;

/**
 * Class RefreshBalance
 * @package Bavix\Wallet\Commands
 * @codeCoverageIgnore
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
     */
    public function handle(): void
    {
        DB::transaction(function() {
            $wallet = config('wallet.wallet.table');
            $trans = config('wallet.transaction.table');

            $availableBalance = DB::table($trans)
                ->select('wallet_id', DB::raw('sum(amount) balance'))
                ->where('confirmed', true)
                ->groupBy('wallet_id');

            $joinClause = function(JoinClause $join) use ($wallet) {
                $join->on("$wallet.id", '=', 'b.wallet_id');
            };

            DB::table($wallet)->update(['balance' => 0]);
            DB::table($wallet)
                ->joinSub($availableBalance, 'b', $joinClause, null, null, 'left')
                ->update(['balance' => DB::raw('b.balance')]);
        });
    }

}
