<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class CreateWalletsTable extends Migration
{

    /**
     * @return string
     */
    protected function table(): string
    {
        return (new Wallet())->getTable();
    }

    /**
     * @return void
     */
    public function up(): void
    {
        Schema::create($this->table(), function(Blueprint $table) {
            $table->increments('id');
            $table->morphs('holder');
            $table->string('name');
            $table->string('slug')->index();
            $table->string('description')->nullable();
            $table->bigInteger('balance')->default(0);
            $table->timestamps();

            $table->unique(['holder_type', 'holder_id', 'slug']);
        });

        /**
         * migrate v1 to v2
         */
        $default = config('wallet.wallet.default.name', 'Default Wallet');
        $slug = config('wallet.wallet.default.slug', 'default');
        $query = Transaction::query()->distinct()
            ->selectRaw('payable_type as holder_type')
            ->selectRaw('payable_id as holder_id')
            ->selectRaw('? as name', [$default])
            ->selectRaw('? as slug', [$slug])
            ->selectRaw('sum(amount) as balance')
            ->selectRaw('? as created_at', [\Carbon\Carbon::now()])
            ->selectRaw('? as updated_at', [\Carbon\Carbon::now()])
            ->groupBy('holder_type', 'holder_id')
            ->orderBy('holder_type');

        DB::transaction(function () use ($query) {
            $query->chunk(1000, function (Collection $transactions) {
                DB::table((new Wallet())->getTable())
                    ->insert($transactions->toArray());
            });
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::drop($this->table());
    }

}
