<?php

use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateTransfersTable extends Migration
{

    /**
     * @return void
     */
    public function up(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            $table->boolean('refund')
                ->after('withdraw_id')
                ->default(0);

            $table->index(['from_type', 'from_id', 'to_type', 'to_id', 'refund'], 'from_to_refund_ind');
            $table->index(['from_type', 'from_id', 'refund'], 'from_refund_ind');
            $table->index(['to_type', 'to_id', 'refund'], 'to_refund_ind');
        });
    }

    /**
     * @return string
     */
    protected function table(): string
    {
        return (new Transfer())->getTable();
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            if (!(DB::connection() instanceof SQLiteConnection)) {
                $table->dropIndex('from_to_refund_ind');
                $table->dropIndex('from_refund_ind');
                $table->dropIndex('to_refund_ind');
            }

            $table->dropColumn('refund');
        });
    }

}
