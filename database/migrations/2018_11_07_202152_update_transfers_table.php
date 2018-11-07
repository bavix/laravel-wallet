<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Bavix\Wallet\Models\Transfer;

class UpdateTransfersTable extends Migration
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
        Schema::table($this->table(), function(Blueprint $table) {
            $table->boolean('refund')->default(0);
        });
    }

    /**
     * @return void
     */
    public function down(): void
    {
        Schema::table($this->table(), function (Blueprint $table) {
            $table->dropColumn('refund');
        });
    }

}
