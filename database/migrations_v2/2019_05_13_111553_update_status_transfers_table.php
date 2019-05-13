<?php

use Bavix\Wallet\Models\Transfer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateStatusTransfersTable extends Migration
{

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
    public function up(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            $table->renameColumn('status', 'tmpStatus');
        });

        Schema::table($this->table(), function (Blueprint $table) {
            $table->renameColumn('status_last', 'tmpStatusLast');
        });

        Schema::table($this->table(), function (Blueprint $table) {
            $enums = [
                Transfer::STATUS_TRANSFER,
                Transfer::STATUS_PAID,
                Transfer::STATUS_REFUND,
                Transfer::STATUS_GIFT,
            ];

            $table->enum('status', $enums)
                ->default(Transfer::STATUS_TRANSFER);

            $table->enum('status_last', $enums)
                ->nullable();
        });

        DB::table($this->table())
            ->update([
                'status' => DB::raw('tmpStatus'),
                'status_last' => DB::raw('tmpStatusLast'),
            ]);

        Schema::table($this->table(), function (Blueprint $table) {
            $table->dropColumn('tmpStatus');
        });

        Schema::table($this->table(), function (Blueprint $table) {
            $table->dropColumn('tmpStatusLast');
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            $table->renameColumn('status', 'tmpStatus');
        });

        Schema::table($this->table(), function (Blueprint $table) {
            $table->renameColumn('status_last', 'tmpStatusLast');
        });

        Schema::table($this->table(), function (Blueprint $table) {
            $enums = [
                Transfer::STATUS_PAID,
                Transfer::STATUS_REFUND,
                Transfer::STATUS_GIFT,
            ];

            $table->enum('status', $enums)
                ->default(Transfer::STATUS_PAID);

            $table->enum('status_last', $enums)
                ->nullable();
        });

        DB::table($this->table())
            ->where('tmpStatus', Transfer::STATUS_TRANSFER)
            ->update(['tmpStatus' => Transfer::STATUS_PAID]);

        DB::table($this->table())
            ->where('tmpStatusLast', Transfer::STATUS_TRANSFER)
            ->update(['tmpStatusLast' => Transfer::STATUS_PAID]);

        DB::table($this->table())
            ->update([
                'status' => DB::raw('tmpStatus'),
                'status_last' => DB::raw('tmpStatusLast'),
            ]);

        Schema::table($this->table(), function (Blueprint $table) {
            $table->dropColumn('tmpStatus');
        });

        Schema::table($this->table(), function (Blueprint $table) {
            $table->dropColumn('tmpStatusLast');
        });
    }

}
