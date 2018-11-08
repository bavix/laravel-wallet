<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Transfer;

class CreateTransfersTable extends Migration
{

    /**
     * @return string
     */
    protected function transactionTable(): string
    {
        return (new Transaction())->getTable();
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
    public function up(): void
    {
        Schema::create($this->table(), function(Blueprint $table) {
            $table->increments('id');
            $table->morphs('from');
            $table->morphs('to');
            $table->unsignedInteger('deposit_id');
            $table->unsignedInteger('withdraw_id');
            $table->uuid('uuid')->unique();
            $table->timestamps();

            $table->foreign('deposit_id')
                ->references('id')
                ->on($this->transactionTable())
                ->onDelete('cascade');

            $table->foreign('withdraw_id')
                ->references('id')
                ->on($this->transactionTable())
                ->onDelete('cascade');
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
